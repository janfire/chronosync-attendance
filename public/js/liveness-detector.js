/**
 * LivenessDetector
 * Uses MediaPipe Face Mesh to detect blinks via Eye Aspect Ratio (EAR)
 * to prevent presentation attacks (photos/videos) with zero backend overhead.
 */
class LivenessDetector {
    constructor() {
        this.faceMesh = null;
        this.camera = null;
        this.isInitialized = false;
        this.onBlinkDetected = null;
        this.onFaceDetected = null;
        this.onStatusChange = null;
        
        this.blinkState = 'open'; // 'open', 'closed'
        this.blinkCount = 0;
        this.requiredBlinks = 1;
        
        // EAR threshold: Below this is considered a closed eye.
        this.EAR_THRESHOLD = 0.22; 
    }

    async init(videoElement) {
        if (this.isInitialized) return;
        
        this._updateStatus('Loading anti-spoofing models...');

        try {
            // MediaPipe requires the globals FaceMesh and Camera to be loaded via CDN.
            if (typeof window.FaceMesh === 'undefined' || typeof window.Camera === 'undefined') {
                throw new Error("MediaPipe libraries not loaded.");
            }

            this.faceMesh = new window.FaceMesh({locateFile: (file) => {
                return `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`;
            }});

            this.faceMesh.setOptions({
                maxNumFaces: 1,
                refineLandmarks: true, // Needed for accurate eye tracking
                minDetectionConfidence: 0.6,
                minTrackingConfidence: 0.6
            });

            this.faceMesh.onResults((results) => this._onResults(results));

            this.camera = new window.Camera(videoElement, {
                onFrame: async () => {
                    if (this.isInitialized && this.faceMesh) {
                        await this.faceMesh.send({image: videoElement});
                    }
                },
                width: 640,
                height: 480
            });

            await this.camera.start();
            this.isInitialized = true;
            this._updateStatus('Ready: Please blink to confirm identity');

        } catch (error) {
            console.error("Failed to initialize Liveness Detector:", error);
            this._updateStatus('Error loading security modules');
            throw error;
        }
    }

    stop() {
        this.isInitialized = false;
        if (this.camera) {
            this.camera.stop();
        }
        if (this.faceMesh) {
            this.faceMesh.close();
        }
        this.blinkCount = 0;
        this.blinkState = 'open';
    }

    reset() {
        this.blinkCount = 0;
        this.blinkState = 'open';
        this._updateStatus('Please blink to confirm identity');
    }

    _onResults(results) {
        if (!results.multiFaceLandmarks || results.multiFaceLandmarks.length === 0) {
            if (this.onFaceDetected) this.onFaceDetected(false);
            this._updateStatus('No face detected');
            return;
        }

        if (this.onFaceDetected) this.onFaceDetected(true);

        const landmarks = results.multiFaceLandmarks[0];

        // MediaPipe FaceMesh eye landmark indices
        // Left Eye: 33, 160, 158, 133, 153, 144
        // Right Eye: 362, 385, 387, 263, 373, 380
        const leftEye = [33, 160, 158, 133, 153, 144].map(idx => landmarks[idx]);
        const rightEye = [362, 385, 387, 263, 373, 380].map(idx => landmarks[idx]);

        const leftEAR = this._calculateEAR(leftEye);
        const rightEAR = this._calculateEAR(rightEye);
        const avgEAR = (leftEAR + rightEAR) / 2.0;

        // Blink detection logic
        if (avgEAR < this.EAR_THRESHOLD) {
            if (this.blinkState === 'open') {
                this.blinkState = 'closed';
            }
        } else {
            if (this.blinkState === 'closed') {
                // Eye opened after being closed -> Blink complete
                this.blinkState = 'open';
                this.blinkCount++;
                
                if (this.blinkCount >= this.requiredBlinks) {
                    this._updateStatus('Liveness confirmed! Capturing...');
                    if (this.onBlinkDetected) {
                        this.onBlinkDetected();
                    }
                    // Prevent multiple rapid triggers
                    this.blinkCount = 0; 
                }
            }
        }
    }

    _calculateEAR(eye) {
        // Euclidean distances between vertical eye landmarks
        const v1 = this._euclideanDistance(eye[1], eye[5]);
        const v2 = this._euclideanDistance(eye[2], eye[4]);
        // Euclidean distance between horizontal eye landmarks
        const h = this._euclideanDistance(eye[0], eye[3]);
        // EAR formula
        return (v1 + v2) / (2.0 * h);
    }

    _euclideanDistance(point1, point2) {
        const dx = point1.x - point2.x;
        const dy = point1.y - point2.y;
        return Math.sqrt(dx * dx + dy * dy);
    }

    _updateStatus(message) {
        if (this.onStatusChange) {
            this.onStatusChange(message);
        }
    }
}

// Export for global usage or module system
window.LivenessDetector = LivenessDetector;
