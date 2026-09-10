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
        
        // Adaptive Calibration State
        this.isCalibrating = true;
        this.calibrationFrames = 0;
        this.maxCalibrationFrames = 10;
        this.calibrationEARSum = 0;
        this.dynamicThreshold = 0.22; // Fallback starting point
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
            this.reset(); // Starts the calibration process

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
        
        // Reset calibration
        this.isCalibrating = true;
        this.calibrationFrames = 0;
        this.calibrationEARSum = 0;
    }

    reset() {
        this.blinkCount = 0;
        this.blinkState = 'open';
        
        // Restart calibration process
        this.isCalibrating = true;
        this.calibrationFrames = 0;
        this.calibrationEARSum = 0;
        this._updateStatus('Calibrating... Please look at the camera');
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

        // --- Adaptive Calibration Phase ---
        if (this.isCalibrating) {
            // Outlier filter: only use frames where the eye is reasonably open (> 0.15 EAR)
            if (avgEAR > 0.15) {
                this.calibrationEARSum += avgEAR;
                this.calibrationFrames++;
                
                if (this.calibrationFrames >= this.maxCalibrationFrames) {
                    // Set threshold to 75% of the average resting EAR
                    let calculated = (this.calibrationEARSum / this.maxCalibrationFrames) * 0.75;
                    // Clamp between 0.15 and 0.25 to prevent permanent lockouts
                    this.dynamicThreshold = Math.max(0.15, Math.min(0.25, calculated));
                    
                    this.isCalibrating = false;
                    this._updateStatus('Ready: Please blink to confirm identity');
                }
            }
            return; // Skip normal blink detection during calibration
        }

        // --- Blink Detection Logic ---
        if (avgEAR < this.dynamicThreshold) {
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
                    
                    // Calculate bounding box for network payload optimization
                    let minX = 1.0, minY = 1.0, maxX = 0.0, maxY = 0.0;
                    for (const pt of landmarks) {
                        if (pt.x < minX) minX = pt.x;
                        if (pt.x > maxX) maxX = pt.x;
                        if (pt.y < minY) minY = pt.y;
                        if (pt.y > maxY) maxY = pt.y;
                    }
                    
                    if (this.onBlinkDetected) {
                        this.onBlinkDetected({ minX, minY, maxX, maxY });
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
