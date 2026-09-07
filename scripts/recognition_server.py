import http.server
import json
import socketserver
import sys
import os
import base64
from io import BytesIO
import time
import concurrent.futures
import numpy as np
import cv2
import face_recognition
from PIL import Image, ImageOps
PORT = 5001
HOST = "0.0.0.0"

# Global variable to store loaded models (technically face_recognition loads lazily, 
# but we can force a load by running a dummy image)
start_time = time.time()
# Global dictionary to hold user encodings in RAM for fast 1:N matching
FACE_DATABASE = {}
# Create a dummy image to force model loading
dummy_image = np.zeros((100, 100, 3), dtype=np.uint8)
try:
    face_recognition.face_locations(dummy_image, model="hog")
    print(f"Models loaded in {time.time() - start_time:.2f} seconds.")
except Exception as e:
    print(f"Warning: Model pre-loading failed: {e}")

class RecognitionHandler(http.server.BaseHTTPRequestHandler):
    def _send_response(self, data, status=200):
        self.send_response(status)
        self.send_header('Content-type', 'application/json')
        self.end_headers()
        self.wfile.write(json.dumps(data).encode('utf-8'))

    def do_POST(self):
        content_length = int(self.headers['Content-Length'])
        post_data = self.rfile.read(content_length)
        
        try:
            request = json.loads(post_data.decode('utf-8'))
        except json.JSONDecodeError:
            self._send_response({'error': 'Invalid JSON'}, 400)
            return

        action = request.get('action')
        
        if action == 'extract':
            self.handle_extract(request)
        elif action == 'sync':
            self.handle_sync(request)
        elif action == 'recognize':
            self.handle_recognize(request)
        elif action == 'status':
            self._send_response({'status': 'running', 'models_loaded': True, 'faces_in_memory': len(FACE_DATABASE)})
        else:
            self._send_response({'error': f'Unknown action: {action}'}, 400)

    def handle_extract(self, request):
        image_data = request.get('image')
        if not image_data:
            self._send_response({'error': 'No image provided'}, 400)
            return

        # is_enrollment=True  → CNN face detector + 10-jitter averaging (high quality, slower)
        # is_enrollment=False → HOG face detector + 1-jitter            (fast path for scanning)
        is_enrollment = bool(request.get('is_enrollment', False))

        try:
            result = self.process_image(image_data, is_enrollment=is_enrollment)
            self._send_response(result)
        except Exception as e:
            self._send_response({'error': str(e)}, 500)

    def handle_sync(self, request):
        global FACE_DATABASE
        tenant_id = str(request.get('tenant_id', 'default'))
        templates = request.get('templates')
        if templates is None:
            self._send_response({'error': 'No templates provided'}, 400)
            return
            
        if tenant_id not in FACE_DATABASE:
            FACE_DATABASE[tenant_id] = {}
        else:
            FACE_DATABASE[tenant_id].clear()
            
        if isinstance(templates, list):
            if len(templates) == 0:
                templates = {}
            else:
                self._send_response({'error': 'Templates must be a dictionary'}, 400)
                return

        for user_id_str, enc_list in templates.items():
            FACE_DATABASE[tenant_id][int(user_id_str)] = np.array(enc_list)
            
        print(f"Synced {len(FACE_DATABASE[tenant_id])} faces into memory for tenant {tenant_id}.")
        self._send_response({'success': True, 'count': len(FACE_DATABASE[tenant_id])})

    def handle_recognize(self, request):
        global FACE_DATABASE
        tenant_id = str(request.get('tenant_id', 'default'))
        image_data = request.get('image')
        tolerance = float(request.get('tolerance', 0.38)) # Default tolerance from Laravel

        if not image_data:
            self._send_response({'error': 'No image provided'}, 400)
            return

        tenant_db = FACE_DATABASE.get(tenant_id, {})
        if len(tenant_db) == 0:
            self._send_response({'error': 'FACE_DATABASE_EMPTY', 'message': f'Python memory for tenant {tenant_id} is empty. Please sync templates.'}, 400)
            return

        try:
            # Extract features from new image
            result = self.process_image(image_data, is_enrollment=False)
            
            if 'error' in result:
                self._send_response(result)
                return

            unknown_encoding = np.array(result['facial_encoding'])
            
            # 1:N Math directly in numpy
            t0 = time.time()
            known_ids = list(tenant_db.keys())
            known_encodings = list(tenant_db.values())
            
            # Calculate euclidean distance (L2 norm) for all faces at once
            distances = np.linalg.norm(known_encodings - unknown_encoding, axis=1)
            
            best_match_index = np.argmin(distances)
            min_distance = distances[best_match_index]
            
            match_time_ms = (time.time() - t0) * 1000
            
            if min_distance <= tolerance:
                best_user_id = known_ids[best_match_index]
                self._send_response({
                    'success': True,
                    'match': True,
                    'user_id': best_user_id,
                    'distance': float(min_distance),
                    'match_time_ms': match_time_ms
                })
            else:
                self._send_response({
                    'success': True,
                    'match': False,
                    'message': 'No matching face found within tolerance',
                    'match_time_ms': match_time_ms
                })

        except Exception as e:
            self._send_response({'error': str(e)}, 500)

    def process_image(self, base64_data, is_enrollment=False):
        # Decode base64
        if ',' in base64_data:
            base64_data = base64_data.split(',', 1)[1]
        
        image_bytes = base64.b64decode(base64_data)
        image_pil = Image.open(BytesIO(image_bytes))

        # Optimization: Resize if too huge
        max_size = 800
        if max(image_pil.size) > max_size:
            ratio = max_size / max(image_pil.size)
            new_size = (int(image_pil.size[0] * ratio), int(image_pil.size[1] * ratio))
            # Safe resampling
            try:
                resample = Image.Resampling.LANCZOS
            except AttributeError:
                resample = Image.LANCZOS
            image_pil = image_pil.resize(new_size, resample)

        # --- Luminance normalization ---
        # Compensates for webcam exposure variance (bright daylight vs fluorescent office).
        # autocontrast stretches the luminance histogram without altering color relationships,
        # making encodings more consistent across different lighting conditions.
        image_pil = image_pil.convert('RGB')  # Ensure RGB before autocontrast
        image_pil = ImageOps.autocontrast(image_pil, cutoff=1)

        # Convert to numpy array for face_recognition
        image = np.array(image_pil)

        # --- Detector selection ---
        # Both Enrollment and Scanning now use HOG for maximum CPU performance.
        # number_of_times_to_upsample=2 helps find slightly smaller faces.
        face_locations = face_recognition.face_locations(image, model="hog", number_of_times_to_upsample=2)

        if len(face_locations) == 0:
            return {'error': 'No face detected'}
        if len(face_locations) > 1:
            return {'error': 'Multiple faces detected'}

        # --- Minimum face-size guard ---
        # A face bounding box smaller than 80x80px indicates the user is too far from the
        # camera. The resulting encoding is too noisy to store as a reliable template.
        top, right, bottom, left = face_locations[0]
        face_h = bottom - top
        face_w = right - left
        if face_h < 80 or face_w < 80:
            return {
                'error': (
                    f'Face is too small ({face_w}x{face_h}px). '
                    'Please move closer to the camera for a better capture.'
                )
            }

        if is_enrollment:
            # Enforce strict frontal face for high-quality baseline templates
            landmarks = face_recognition.face_landmarks(image, face_locations)
            if landmarks:
                lm = landmarks[0]
                left_eye_x = sum([p[0] for p in lm['left_eye']]) / len(lm['left_eye'])
                right_eye_x = sum([p[0] for p in lm['right_eye']]) / len(lm['right_eye'])
                nose_x = lm['nose_bridge'][3][0] # Bottom of nose bridge
                
                dist_1 = abs(nose_x - left_eye_x)
                dist_2 = abs(nose_x - right_eye_x)
                
                max_dist = max(dist_1, dist_2)
                min_dist = min(dist_1, dist_2)
                
                if max_dist > 0:
                    ratio = min_dist / max_dist
                    # Ratio < 0.55 indicates significant yaw (head turn)
                    if ratio < 0.55:
                        return {'error': 'Face is turned to the side. Please look directly at the camera.'}

        # Enrollment and Scanning now both use num_jitters=1 for maximum speed.
        num_jitters = 1
        face_encodings = face_recognition.face_encodings(image, face_locations, num_jitters=num_jitters)
        
        if len(face_encodings) == 0:
            return {'error': 'Could not encode face'}

        return {
            'success': True,
            'facial_encoding': face_encodings[0].tolist(),
            'face_location': face_locations[0],
            'encoding_dimensions': len(face_encodings[0])
        }

import threading

class ThreadPoolTCPServer(socketserver.ThreadingMixIn, socketserver.TCPServer):
    """
    TCP Server that processes requests concurrently using ThreadingMixIn, 
    but safely capped with a BoundedSemaphore to prevent memory exhaustion.
    """
    daemon_threads = True
    
    def __init__(self, server_address, RequestHandlerClass, bind_and_activate=True):
        super().__init__(server_address, RequestHandlerClass, bind_and_activate)
        
        # Hardware-aware dynamic scaling with a manual override
        env_workers = os.environ.get('MAX_FACE_WORKERS')
        if env_workers and env_workers.isdigit():
            self.max_workers = int(env_workers)
        else:
            # Default to min(CPU Cores, 4) to be safe out-of-the-box
            cpu_count = os.cpu_count() or 1
            self.max_workers = min(cpu_count, 4)
            
        print(f"Server configured with max_workers={self.max_workers}")
        self._pool = threading.BoundedSemaphore(value=self.max_workers)

    def process_request(self, request, client_address):
        """Acquire semaphore before spawning thread. Blocks if at max capacity."""
        self._pool.acquire()
        t = threading.Thread(target=self.process_request_thread_with_sem, args=(request, client_address))
        t.daemon = self.daemon_threads
        t.start()
        
    def process_request_thread_with_sem(self, request, client_address):
        """Process request and release semaphore when done."""
        try:
            self.process_request_thread(request, client_address)
        finally:
            self._pool.release()

if __name__ == "__main__":
    print(f"Starting Facial Recognition Server on {HOST}:{PORT}")
    with ThreadPoolTCPServer((HOST, PORT), RecognitionHandler) as httpd:
        print("Server running. Press Ctrl+C to stop.")
        try:
            httpd.serve_forever()
        except KeyboardInterrupt:
            print("\nShutting down server...")
            httpd.server_close()
