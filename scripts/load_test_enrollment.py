import requests
import base64
import time
import concurrent.futures
import re
import urllib3
import json
import logging

urllib3.disable_warnings()
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

# Constants
CONCURRENT_USERS = 50
LARAVEL_URL = "http://127.0.0.1:8000"
IMAGE_URL = "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=300&auto=format&fit=crop"

def get_base64_image():
    logging.info("Downloading sample face image...")
    res = requests.get(IMAGE_URL)
    if res.status_code == 200:
        return "data:image/jpeg;base64," + base64.b64encode(res.content).decode('utf-8')
    raise Exception("Failed to download sample image")

def simulate_user(user_id, base64_image):
    session = requests.Session()
    
    try:
        # 1. Get CSRF token
        start_time = time.time()
        res = session.get(f"{LARAVEL_URL}/register")
        if res.status_code != 200:
            return False, f"GET /register failed: {res.status_code}", 0
            
        # Extract CSRF token from input tag
        match = re.search(r'name="_token" value="([^"]+)"', res.text)
        if not match:
            # Maybe the page was throttled? 
            if res.status_code == 429:
                return False, "Throttled on GET /register", 0
            return False, "CSRF token not found in input", 0
        csrf_token = match.group(1)
        
        # 2. Register dummy user (bypass uniqueness by appending ID and timestamp)
        email = f"loadtest_{int(time.time())}_{user_id}@example.com"
        emp_num = f"LT-{int(time.time())}-{user_id}"
        
        reg_data = {
            "_token": csrf_token,
            "is_guest": "1",
            "name": f"Load Tester {user_id}",
            "email": email,
            "employee_number": emp_num,
            "password": "password123",
            "password_confirmation": "password123"
        }
        
        # We don't care about the redirect, just that it creates the pending_registration session
        reg_res = session.post(f"{LARAVEL_URL}/register", data=reg_data)
        if reg_res.status_code == 429:
            return False, "Throttled on POST /register", 0
        
        # 3. Post to enrollment endpoint
        enroll_start = time.time()
        enroll_data = {
            "facial_image": base64_image
        }
        
        # We need CSRF in headers for API requests or ajax
        headers = {
            "X-CSRF-TOKEN": csrf_token,
            "Accept": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        }
        
        enroll_res = session.post(f"{LARAVEL_URL}/biometric/facial/enroll", json=enroll_data, headers=headers)
        duration = time.time() - enroll_start
        
        if enroll_res.status_code == 200:
            return True, f"User {user_id} success", duration
        else:
            return False, f"User {user_id} failed: {enroll_res.status_code} - {enroll_res.text[:100]}", duration
            
    except Exception as e:
        return False, f"User {user_id} error: {str(e)}", 0

def run_load_test():
    logging.info(f"Starting load test with {CONCURRENT_USERS} concurrent users.")
    try:
        base64_image = get_base64_image()
        logging.info("Sample image ready.")
    except Exception as e:
        logging.error(f"Failed to get image: {e}")
        return
        
    start_time = time.time()
    results = []
    
    with concurrent.futures.ThreadPoolExecutor(max_workers=CONCURRENT_USERS) as executor:
        futures = [executor.submit(simulate_user, i, base64_image) for i in range(CONCURRENT_USERS)]
        
        for future in concurrent.futures.as_completed(futures):
            results.append(future.result())
            
    total_time = time.time() - start_time
    
    # Analyze results
    successes = [r for r in results if r[0]]
    failures = [r for r in results if not r[0]]
    durations = [r[2] for r in results if r[0]]
    
    logging.info("\n--- LOAD TEST RESULTS ---")
    logging.info(f"Total Requests: {CONCURRENT_USERS}")
    logging.info(f"Successful: {len(successes)}")
    logging.info(f"Failed: {len(failures)}")
    logging.info(f"Total Time: {total_time:.2f} seconds")
    
    if len(failures) > 0:
        logging.error("Sample Failures:")
        for f in failures[:5]:
            logging.error(f[1])
            
    if durations:
        avg_time = sum(durations) / len(durations)
        max_time = max(durations)
        min_time = min(durations)
        logging.info(f"Average Response Time: {avg_time:.2f}s")
        logging.info(f"Max Response Time: {max_time:.2f}s")
        logging.info(f"Min Response Time: {min_time:.2f}s")

if __name__ == "__main__":
    run_load_test()
