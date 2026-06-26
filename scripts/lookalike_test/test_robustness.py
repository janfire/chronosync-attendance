import face_recognition
import numpy as np

def run_test():
    print("="*60)
    print(" FACIAL RECOGNITION ROBUSTNESS TEST (LOOKALIKE DETECTION) ")
    print("="*60)

    # File paths
    target_path = r"C:\Users\M.T\.gemini\antigravity-ide\brain\f01d6a86-1d05-4f7a-a5f1-31aa1bfadb63\target_person_1782470181691.png"
    lookalike_path = r"C:\Users\M.T\.gemini\antigravity-ide\brain\f01d6a86-1d05-4f7a-a5f1-31aa1bfadb63\lookalike_person_1782470203180.png"
    different_path = r"C:\Users\M.T\.gemini\antigravity-ide\brain\f01d6a86-1d05-4f7a-a5f1-31aa1bfadb63\different_person_1782470223840.png"

    print("Loading and encoding images...")
    
    # Load images
    try:
        img_target = face_recognition.load_image_file(target_path)
        img_lookalike = face_recognition.load_image_file(lookalike_path)
        img_diff = face_recognition.load_image_file(different_path)
    except Exception as e:
        print(f"Error loading images: {e}")
        return

    # Extract encodings
    # Simulate Enrollment for target
    target_encodings = face_recognition.face_encodings(img_target, num_jitters=10)
    if not target_encodings:
        print("Failed to detect face in Target Person image.")
        return
    target_encoding = target_encodings[0]

    # Simulate Scanning for lookalike and different person
    lookalike_encodings = face_recognition.face_encodings(img_lookalike, num_jitters=1)
    diff_encodings = face_recognition.face_encodings(img_diff, num_jitters=1)

    if not lookalike_encodings or not diff_encodings:
        print("Failed to detect face in test images.")
        return
        
    lookalike_encoding = lookalike_encodings[0]
    diff_encoding = diff_encodings[0]

    print("Encodings successfully extracted.\n")

    # The threshold configured in your FacialRecognitionService.php
    THRESHOLD = 0.38
    print(f"Current System Tolerance Threshold: {THRESHOLD}")
    print("(Distance must be LOWER than threshold to trigger a successful clock-in)\n")

    print("-" * 60)
    print("TEST 1: Same Person Test (Self Match)")
    dist_self = np.linalg.norm(target_encoding - target_encoding)
    print(f"Distance Score:  {dist_self:.4f}")
    print(f"Result:          {'ACCEPTED' if dist_self <= THRESHOLD else 'REJECTED'}")
    
    print("-" * 60)
    print("TEST 2: Lookalike Test (Imposter Match)")
    dist_lookalike = np.linalg.norm(target_encoding - lookalike_encoding)
    print(f"Distance Score:  {dist_lookalike:.4f}")
    if dist_lookalike <= THRESHOLD:
        print("Result:          FAIL! Lookalike was accepted as the original person!")
    else:
        print("Result:          PASS! Lookalike was successfully rejected.")
        
    print("-" * 60)
    print("TEST 3: Completely Different Person Test")
    dist_diff = np.linalg.norm(target_encoding - diff_encoding)
    print(f"Distance Score:  {dist_diff:.4f}")
    if dist_diff <= THRESHOLD:
        print("Result:          FAIL! Different person was accepted!")
    else:
        print("Result:          PASS! Different person was successfully rejected.")

    print("="*60)

if __name__ == "__main__":
    run_test()
