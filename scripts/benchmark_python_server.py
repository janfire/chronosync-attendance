import json
import urllib.request
import base64
import time
import random
import urllib.error

def generate_dummy_templates(count=5000):
    print(f"Generating {count} dummy face templates...")
    templates = {}
    for i in range(count):
        # A face encoding is a 128-dimensional vector of floats typically ranging from -0.3 to 0.3
        templates[str(i+1)] = [random.uniform(-0.3, 0.3) for _ in range(128)]
    return templates

def get_base64_face_image():
    # Use a known public image with a clear face
    url = "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&w=400"
    print("Downloading test face image...")
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    with urllib.request.urlopen(req) as response:
        image_data = response.read()
    
    return base64.b64encode(image_data).decode('utf-8')

def post_json(url, data):
    req = urllib.request.Request(url)
    req.add_header('Content-Type', 'application/json; charset=utf-8')
    jsondata = json.dumps(data)
    jsondataasbytes = jsondata.encode('utf-8')
    req.add_header('Content-Length', len(jsondataasbytes))
    
    response = urllib.request.urlopen(req, jsondataasbytes)
    return json.loads(response.read().decode('utf-8'))

def main():
    print("=== Python In-Memory 1:N Load Test ===")
    
    try:
        # 1. Sync 5,000 users
        templates = generate_dummy_templates(5000)
        
        sync_payload = {
            "action": "sync",
            "templates": templates
        }
        
        print("\nSending POST /sync with 5000 templates...")
        t0 = time.time()
        sync_response = post_json('http://localhost:5001', sync_payload)
        sync_time = time.time() - t0
        print(f"Sync complete in {sync_time*1000:.2f} ms")
        print(f"Response: {sync_response}")
        
        # 2. Benchmark Recognition
        b64_image = get_base64_face_image()
        
        recognize_payload = {
            "action": "recognize",
            "image": b64_image,
            "tolerance": 0.38
        }
        
        print("\nSending POST /recognize (Extract Face + Match against 5000 in memory)...")
        # Warmup request
        post_json('http://localhost:5001', recognize_payload)
        
        # Real benchmark
        t1 = time.time()
        recognize_response = post_json('http://localhost:5001', recognize_payload)
        recognize_time = time.time() - t1
        
        print(f"Recognition + 1:N Match complete in {recognize_time*1000:.2f} ms!")
        print(f"Response: {recognize_response}")
        
        print("\n=== Summary ===")
        print(f"Total Database Size: 5000 users")
        print(f"Total Pipeline Latency: {recognize_time*1000:.2f} ms")
        print("(This includes HTTP overhead, Face Extraction (HOG), and 1:5000 Vector Math)")
        
    except Exception as e:
        print(f"Error during benchmark: {e}")

if __name__ == '__main__':
    main()
