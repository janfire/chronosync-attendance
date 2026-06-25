import concurrent.futures
import urllib.request
import json
import time

URL = "http://127.0.0.1:5001"

# Tiny 100x100 dummy jpeg to force the server to do image decoding and run the face detector
img_b64 = "/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCABkAGQBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA="

payload = {
    "action": "extract",
    "image": "data:image/jpeg;base64," + img_b64,
    "is_enrollment": False
}
data = json.dumps(payload).encode('utf-8')

def send_request(req_id):
    start = time.time()
    req = urllib.request.Request(URL, data=data, headers={'Content-Type': 'application/json'})
    try:
        with urllib.request.urlopen(req, timeout=30) as response:
            response.read()
            elapsed = time.time() - start
            return (req_id, response.getcode(), elapsed)
    except Exception as e:
        elapsed = time.time() - start
        return (req_id, f"ERROR: {str(e)}", elapsed)

def run_load_test(concurrent_users=10):
    print(f"Starting Load Test: Firing {concurrent_users} simultaneous facial extraction requests to {URL}...")
    start_time = time.time()
    
    with concurrent.futures.ThreadPoolExecutor(max_workers=concurrent_users) as executor:
        futures = [executor.submit(send_request, i) for i in range(concurrent_users)]
        
        for future in concurrent.futures.as_completed(futures):
            req_id, status, elapsed = future.result()
            print(f"Request {req_id:02d} | Time to complete: {elapsed:.2f} seconds | Status: {status}")

    total_time = time.time() - start_time
    print("-" * 30)
    print("--- LOAD TEST COMPLETE ---")
    print(f"Total processing time for all {concurrent_users} requests: {total_time:.2f} seconds")

if __name__ == "__main__":
    run_load_test(10)
