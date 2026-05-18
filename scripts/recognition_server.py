import http.server
import json
import socketserver
import sys
import os
import base64
from io import BytesIO
import time

import numpy as np
import cv2
import face_recognition
from PIL import Image
PORT = 5001
HOST = "localhost"

# Global variable to store loaded models (technically face_recognition loads lazily, 
# but we can force a load by running a dummy image)
print("Loading Facial Recognition Models... this may take a moment.")
start_time = time.time()
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
        elif action == 'compare':
            self.handle_compare(request)
        elif action == 'status':
            self._send_response({'status': 'running', 'models_loaded': True})
        else:
            self._send_response({'error': f'Unknown action: {action}'}, 400)

    def handle_extract(self, request):
        image_data = request.get('image')
        if not image_data:
            self._send_response({'error': 'No image provided'}, 400)
            return

        try:
            result = self.process_image(image_data)
            self._send_response(result)
        except Exception as e:
            self._send_response({'error': str(e)}, 500)

    def handle_compare(self, request):
        known_encoding = request.get('known_encoding')
        image_data = request.get('image')
        tolerance = request.get('tolerance', 0.6)

        if not known_encoding or not image_data:
            self._send_response({'error': 'Missing known_encoding or image'}, 400)
            return

        try:
            # Extract features from new image
            result = self.process_image(image_data)
            
            if 'error' in result:
                self._send_response(result)
                return

            unknown_encoding = result['facial_encoding']
            
            # Compare
            matches = face_recognition.compare_faces([known_encoding], unknown_encoding, tolerance=tolerance)
            distance = face_recognition.face_distance([known_encoding], unknown_encoding)[0]
            
            confidence = 1 - min(distance, 1.0)

            response = {
                'success': True,
                'match': bool(matches[0]),
                'distance': float(distance),
                'confidence': float(confidence),
                'tolerance_used': tolerance
            }
            self._send_response(response)

        except Exception as e:
            self._send_response({'error': str(e)}, 500)

    def process_image(self, base64_data):
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

        # Convert to RGB
        image = np.array(image_pil.convert('RGB'))

        # Detect faces
        face_locations = face_recognition.face_locations(image, model="hog")

        if len(face_locations) == 0:
            return {'error': 'No face detected'}
        if len(face_locations) > 1:
            return {'error': 'Multiple faces detected'}

        # Encode
        face_encodings = face_recognition.face_encodings(image, face_locations, num_jitters=1)
        
        if len(face_encodings) == 0:
            return {'error': 'Could not encode face'}

        return {
            'success': True,
            'facial_encoding': face_encodings[0].tolist(),
            'face_location': face_locations[0],
            'encoding_dimensions': len(face_encodings[0])
        }

if __name__ == "__main__":
    print(f"Starting Facial Recognition Server on {HOST}:{PORT}")
    with socketserver.TCPServer((HOST, PORT), RecognitionHandler) as httpd:
        print("Server running. Press Ctrl+C to stop.")
        try:
            httpd.serve_forever()
        except KeyboardInterrupt:
            print("\nShutting down server...")
            httpd.server_close()
