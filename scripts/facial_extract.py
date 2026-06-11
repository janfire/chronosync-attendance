import sys
import json
import os
import site
import base64
from io import BytesIO

# Explicitly add user site-packages to sys.path to ensure modules are found
# This is critical when Python is invoked from PHP/Process, which may not
# have the same environment context as interactive shells
def add_user_site_packages():
    """Add user site-packages to sys.path using multiple fallback methods."""
    paths_to_try = []
    paths_added = []
    
    # Method 0: Check PYTHONPATH environment variable first (set by PHP)
    pythonpath = os.getenv('PYTHONPATH')
    if pythonpath:
        # PYTHONPATH can be a list of paths separated by os.pathsep
        for path in pythonpath.split(os.pathsep):
            path = path.strip()
            if path and os.path.exists(path):
                paths_to_try.append(path)
    
    # Method 1: Use site.getusersitepackages() (most reliable when available)
    try:
        user_site = site.getusersitepackages()
        if user_site and os.path.exists(user_site):
            paths_to_try.append(user_site)
    except Exception:
        pass
    
    # Method 2: Manually construct path from APPDATA (Windows fallback)
    try:
        appdata = os.getenv('APPDATA')
        if appdata:
            # Get Python version
            major, minor = sys.version_info[:2]
            manual_path = os.path.join(appdata, 'Python', f'Python{major}{minor}', 'site-packages')
            if os.path.exists(manual_path):
                paths_to_try.append(manual_path)
    except Exception:
        pass
    
    # Method 3: Try common user site-packages locations (Windows)
    try:
        if sys.platform == 'win32':
            major, minor = sys.version_info[:2]
            # Try APPDATA first
            appdata = os.getenv('APPDATA')
            if appdata:
                common_path = os.path.join(appdata, 'Python', f'Python{major}{minor}', 'site-packages')
                if os.path.exists(common_path):
                    paths_to_try.append(common_path)
            # Try expanduser as fallback
            home = os.path.expanduser('~')
            if home:
                common_path = os.path.join(home, 'AppData', 'Roaming', 'Python', f'Python{major}{minor}', 'site-packages')
                if os.path.exists(common_path):
                    paths_to_try.append(common_path)
    except Exception:
        pass
    
    # Add all valid paths to sys.path (remove duplicates while preserving order)
    seen = set()
    current_sys_paths = {os.path.normpath(os.path.abspath(p)) for p in sys.path}
    
    for path in paths_to_try:
        if not path:
            continue
        try:
            # Normalize path to handle different separators
            normalized = os.path.normpath(os.path.abspath(path))
            if normalized not in seen and normalized not in current_sys_paths:
                if os.path.exists(normalized):
                    sys.path.insert(0, normalized)
                    seen.add(normalized)
                    paths_added.append(normalized)
                    current_sys_paths.add(normalized)
        except Exception:
            # Skip invalid paths
            pass
    
    return paths_added

# Add user site-packages before importing modules
paths_added = add_user_site_packages()

# Now import the required modules
# Import numpy first (cv2 depends on it)
try:
    import numpy as np
except ImportError as e:
    # Provide detailed debugging info
    debug_info = {
        'error': str(e),
        'pythonpath_env': os.getenv('PYTHONPATH'),
        'appdata_env': os.getenv('APPDATA'),
        'user_site_from_site': None,
        'paths_added': paths_added,
        'sys_path': sys.path
    }
    try:
        debug_info['user_site_from_site'] = site.getusersitepackages()
    except:
        pass
    
    error_msg = f"Failed to import numpy: {e}. Debug info: {json.dumps(debug_info, indent=2)}"
    print(json.dumps({'error': error_msg}), file=sys.stderr)
    sys.exit(1)

try:
    import cv2
except ImportError as e:
    # Try to provide helpful debugging info
    user_site = None
    try:
        user_site = site.getusersitepackages()
    except:
        pass
    
    error_msg = f"Failed to import cv2: {e}. "
    error_msg += f"Python: {sys.executable}, "
    error_msg += f"User site-packages: {user_site}, "
    error_msg += f"APPDATA: {os.getenv('APPDATA')}, "
    error_msg += f"sys.path contains user site: {user_site in sys.path if user_site else 'N/A'}"
    print(json.dumps({'error': error_msg}), file=sys.stderr)
    sys.exit(1)

try:
    import face_recognition
except ImportError as e:
    error_msg = f"Failed to import face_recognition: {e}"
    print(json.dumps({'error': error_msg}), file=sys.stderr)
    sys.exit(1)

try:
    from PIL import Image, ImageOps
except ImportError as e:
    error_msg = f"Failed to import PIL: {e}"
    print(json.dumps({'error': error_msg}), file=sys.stderr)
    sys.exit(1)

def extract_facial_features_from_base64(base64_data, is_enrollment=False):
    """
    Extract facial features from base64-encoded image data.

    Args:
        base64_data (str): Base64-encoded image data (with or without data URI prefix)
        is_enrollment (bool): When True, uses high-quality settings (CNN + 10-jitter).
                              When False, uses fast settings (HOG + 1-jitter).

    Returns:
        dict: JSON-serializable result with facial encoding or error
    """
    try:
        # Remove data URI prefix if present
        if ',' in base64_data:
            base64_data = base64_data.split(',', 1)[1]
        
        # Decode base64 to bytes
        image_bytes = base64.b64decode(base64_data)
        
        # Convert bytes to numpy array using PIL
        image_pil = Image.open(BytesIO(image_bytes))
        
        # Resize image if too large for faster processing (max 800px on longest side)
        max_size = 800
        if max(image_pil.size) > max_size:
            ratio = max_size / max(image_pil.size)
            new_size = (int(image_pil.size[0] * ratio), int(image_pil.size[1] * ratio))
            try:
                image_pil = image_pil.resize(new_size, Image.Resampling.LANCZOS)
            except AttributeError:
                image_pil = image_pil.resize(new_size, Image.LANCZOS)
        
        # Convert PIL image to RGB numpy array (face_recognition expects RGB)
        image = np.array(image_pil.convert('RGB'))
        
        return _process_image_for_encoding(image, is_enrollment=is_enrollment)
    except Exception as e:
        return {'error': f'Processing failed: {str(e)}'}

def extract_facial_features(image_path, is_enrollment=False):
    """
    Extract facial features from an image file path.

    Args:
        image_path (str): Path to the image file
        is_enrollment (bool): When True, uses high-quality settings (CNN + 10-jitter).

    Returns:
        dict: JSON-serializable result with facial encoding or error
    """
    try:
        # Load image using face_recognition (handles various formats)
        image = face_recognition.load_image_file(image_path)
        
        return _process_image_for_encoding(image, is_enrollment=is_enrollment)
    except Exception as e:
        return {'error': f'Processing failed: {str(e)}'}

def _process_image_for_encoding(image, is_enrollment=False):
    """
    Common processing logic for extracting facial encoding from a numpy image array.

    Args:
        image: numpy array representing the image (RGB format)
        is_enrollment (bool): When True, uses CNN + 10-jitter for a stable enrollment template.
                              When False, uses HOG + 1-jitter for fast attendance scanning.

    Returns:
        dict: JSON-serializable result with facial encoding or error
    """
    try:
        # --- Luminance normalization ---
        # Compensates for webcam exposure variance (bright daylight vs fluorescent office).
        # autocontrast stretches the luminance histogram without altering colour relationships,
        # making encodings more consistent across lighting conditions.
        image_pil = Image.fromarray(image)
        image_pil = ImageOps.autocontrast(image_pil, cutoff=1)
        image = np.array(image_pil)

        # --- Detector selection ---
        # Enrollment: CNN model — handles tilted faces, glasses, partial occlusion.
        #             Much more precise bounding box = better landmark alignment = better encoding.
        # Scanning:   HOG model — ~20x faster, sufficient for frontal live captures.
        #             upsample=2 improves detection of faces that are slightly further away.
        if is_enrollment:
            face_locations = face_recognition.face_locations(image, model="cnn")
        else:
            face_locations = face_recognition.face_locations(image, model="hog", number_of_times_to_upsample=2)

        # Validate single face detection
        if len(face_locations) == 0:
            return {'error': 'No face detected in image'}
        elif len(face_locations) > 1:
            return {'error': 'Multiple faces detected - please ensure only one person is in frame'}

        # --- Minimum face-size guard ---
        # A bounding box smaller than 80x80px means the user is too far from the camera.
        # Encoding from such a small region is too noisy for a reliable template.
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

        # --- Encoding ---
        # Enrollment: num_jitters=10 averages 10 perturbations → stable centroid template.
        # Scanning:   num_jitters=1  → fast single-shot, acceptable for live comparison.
        num_jitters = 10 if is_enrollment else 1
        face_encodings = face_recognition.face_encodings(image, face_locations, num_jitters=num_jitters)

        if len(face_encodings) == 0:
            return {'error': 'Could not encode facial features - try different lighting/angle'}

        # Convert numpy array to Python list for JSON serialization
        encoding = face_encodings[0].tolist()

        return {
            'success': True,
            'facial_encoding': encoding,       # 128-float array
            'face_location': face_locations[0], # (top, right, bottom, left)
            'encoding_dimensions': len(encoding) # Should be 128
        }
    except Exception as e:
        return {'error': f'Processing failed: {str(e)}'}

def compare_faces(known_encoding, unknown_image_path, tolerance=0.6):
    """
    Compare a known facial encoding against a new image.
    Optimized for speed with reduced model complexity.

    Args:
        known_encoding (list): Previously stored facial encoding
        unknown_image_path (str): Path to image for comparison
        tolerance (float): Similarity threshold (0.0-1.0, lower = stricter)

    Returns:
        dict: Match result with confidence score
    """
    try:
        # Load and process unknown image with optimized settings
        unknown_image = face_recognition.load_image_file(unknown_image_path)
        unknown_face_locations = face_recognition.face_locations(unknown_image, model="hog", number_of_times_to_upsample=1)
        unknown_encodings = face_recognition.face_encodings(unknown_image, unknown_face_locations, num_jitters=1)

        if len(unknown_encodings) == 0:
            return {'error': 'No face detected in verification image'}

        # Compare faces using Euclidean distance
        # Returns True/False based on tolerance
        results = face_recognition.compare_faces([known_encoding], unknown_encodings[0], tolerance=tolerance)

        # Calculate distance (lower = more similar)
        face_distances = face_recognition.face_distance([known_encoding], unknown_encodings[0])
        distance = face_distances[0]

        # Convert distance to confidence score (0-1, higher = better match)
        confidence = 1 - min(distance, 1.0)  # Cap at 1.0

        return {
            'success': True,
            'match': bool(results[0]),
            'distance': distance,
            'confidence': confidence,
            'tolerance_used': tolerance
        }

    except Exception as e:
        return {'error': f'Comparison failed: {str(e)}'}

# Command-line interface for Laravel Process execution
if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'Usage: python facial_extract.py <action> [image_path] [mode: enroll|scan]'}))
        sys.exit(1)

    action = sys.argv[1]

    # Read image input from args first (preferred for stability), then stdin
    image_input = None

    # Check if a file path or data was passed as an argument
    if len(sys.argv) >= 3:
        image_input = sys.argv[2]

    # If no argument, checks stdin
    elif not sys.stdin.isatty():
        # Data is being piped via stdin
        try:
            image_input = sys.stdin.read()
        except Exception as e:
            print(json.dumps({'error': f'Failed to read from stdin: {str(e)}'}))
            sys.exit(1)
            
    if not image_input:
        print(json.dumps({'error': 'No image data provided (neither stdin nor command line argument)'}))
        sys.exit(1)

    # Optional mode argument passed as 4th arg by PHP: 'enroll' or 'scan'.
    # Defaults to 'scan' for backward compatibility with any direct CLI calls.
    mode = sys.argv[3] if len(sys.argv) >= 4 else 'scan'
    is_enrollment = (mode == 'enroll')

    if action == 'extract':
        # Check if input is base64 data (starts with data: or is long base64 string) or file path
        if image_input.startswith('data:') or (len(image_input) > 100 and not os.path.exists(image_input)):
            result = extract_facial_features_from_base64(image_input, is_enrollment=is_enrollment)
        else:
            if not os.path.exists(image_input):
                print(json.dumps({'error': f'Image file not found: {image_input}'}))
                sys.exit(1)
            result = extract_facial_features(image_input, is_enrollment=is_enrollment)

    elif action == 'compare':
        # Verification: compare against stored encoding
        if len(sys.argv) < 4:
            result = {'error': 'Known encoding required for comparison'}
        else:
            # Parse JSON-encoded known encoding from command line
            try:
                known_encoding = json.loads(sys.argv[3])
                # Check if image_input is base64 or file path
                if image_input.startswith('data:') or (len(image_input) > 100 and not os.path.exists(image_input)):
                    # For comparison, we still need file path for now (can be enhanced later)
                    result = {'error': 'File path required for comparison'}
                else:
                    if not os.path.exists(image_input):
                        print(json.dumps({'error': f'Image file not found: {image_input}'}))
                        sys.exit(1)
                    result = compare_faces(known_encoding, image_input)
            except json.JSONDecodeError:
                result = {'error': 'Invalid known encoding format'}
    else:
        result = {'error': f'Invalid action: {action}. Use "extract" or "compare"'}

    # Output JSON result for Laravel to parse
    print(json.dumps(result))
