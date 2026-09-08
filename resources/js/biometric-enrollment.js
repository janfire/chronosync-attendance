import ZKTecoService from './zkteco-service.js';

document.addEventListener('DOMContentLoaded', function () {
    const zkTecoService = new ZKTecoService();
    let currentFingerprintMethod = 'zkteco'; // Default to Direct Agent
    const elements = {
        fingerprintSection: document.getElementById('fingerprint-section'),
        facialSection: document.getElementById('facial-section'),
        completionSection: document.getElementById('enrollment-complete'),
        consentSection: document.getElementById('consent-section'),
        biometricConsent: document.getElementById('biometric-consent'),
        cameraContainer: document.getElementById('camera-container'),

        // Fingerprint elements
        startFingerprintBtn: document.getElementById('start-fingerprint-scan'),
        fingerprintSuccess: document.getElementById('fingerprint-success'),
        fingerprintError: document.getElementById('fingerprint-error'),
        fingerprintErrorText: document.getElementById('fingerprint-error-text'),
        fingerprintStatusMsg: document.getElementById('fingerprint-status-message'),

        // Facial elements
        video: document.getElementById('facial-video'),
        canvas: document.getElementById('facial-canvas'),
        statusText: document.getElementById('status-text'),
        statusMessage: document.getElementById('status-message'),
        processingMessage: document.getElementById('processing-message'),
        facialSuccess: document.getElementById('facial-success'),
        facialError: document.getElementById('facial-error'),
        facialErrorText: document.getElementById('error-text'),
        retryFacialBtn: document.getElementById('retry-facial'),
        progressContainer: document.getElementById('facial-progress-container'),
        progressBar: document.getElementById('facial-progress-bar')
    };

    let stream = null;
    let isProcessing = false;
    let captureInterval = null;
    const CAPTURE_INTERVAL_MS = 2500;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // --- UI HELPERS ---

    function updateMethodUI(method) {
        const facialBtn = document.getElementById('btn-method-facial');
        const fingerprintBtn = document.getElementById('btn-method-fingerprint');

        if (facialBtn) {
            facialBtn.classList.toggle('bg-white', method === 'facial');
            facialBtn.classList.toggle('shadow-sm', method === 'facial');
            facialBtn.classList.toggle('text-blue-600', method === 'facial');
            facialBtn.classList.toggle('text-gray-500', method !== 'facial');
        }

        if (fingerprintBtn) {
            fingerprintBtn.classList.toggle('bg-white', method === 'fingerprint');
            fingerprintBtn.classList.toggle('shadow-sm', method === 'fingerprint');
            fingerprintBtn.classList.toggle('text-blue-600', method === 'fingerprint');
            fingerprintBtn.classList.toggle('text-gray-500', method !== 'fingerprint');
        }

        if (elements.facialSection) elements.facialSection.classList.toggle('hidden', method !== 'facial');
        if (elements.fingerprintSection) elements.fingerprintSection.classList.toggle('hidden', method !== 'fingerprint');
    }

    window.switchMethod = function (method) {
        updateMethodUI(method);
        if (method === 'facial') {
            if (elements.biometricConsent && !elements.biometricConsent.checked) {
                if (elements.consentSection) elements.consentSection.classList.remove('hidden');
                if (elements.cameraContainer) elements.cameraContainer.classList.add('hidden');
                if (elements.statusText) elements.statusText.textContent = 'Please agree to the privacy policy to continue.';
            } else {
                if (elements.consentSection) elements.consentSection.classList.add('hidden');
                if (elements.cameraContainer) elements.cameraContainer.classList.remove('hidden');
                startCamera();
            }
        } else {
            stopCamera();
        }
    };

    if (elements.biometricConsent) {
        elements.biometricConsent.addEventListener('change', (e) => {
            if (e.target.checked) {
                if (elements.consentSection) elements.consentSection.classList.add('hidden');
                if (elements.cameraContainer) elements.cameraContainer.classList.remove('hidden');
                startCamera();
            }
        });
    }

    // --- EVENT LISTENERS ---

    const facialBtn = document.getElementById('btn-method-facial');
    const fingerprintBtn = document.getElementById('btn-method-fingerprint');

    if (facialBtn) facialBtn.addEventListener('click', () => switchMethod('facial'));
    if (fingerprintBtn) fingerprintBtn.addEventListener('click', () => switchMethod('fingerprint'));

    if (elements.startFingerprintBtn) {
        elements.startFingerprintBtn.addEventListener('click', startFingerprintEnrollment);
    }

    if (elements.retryFacialBtn) {
        elements.retryFacialBtn.addEventListener('click', () => {
            if (elements.facialError) elements.facialError.classList.add('hidden');
            if (elements.statusMessage) elements.statusMessage.classList.remove('hidden');
            if (elements.statusText) elements.statusText.textContent = 'Reinitializing camera...';
            if (elements.cameraContainer) elements.cameraContainer.classList.remove('hidden');
            startCamera();
        });
    }

    // --- METHOD TOGGLE ---
    const methodZkBtn = document.getElementById('method-zkteco');
    const methodWebAuthnBtn = document.getElementById('method-webauthn');

    function updateFingerprintMethodUI(method) {
        currentFingerprintMethod = method;
        if (methodZkBtn && methodWebAuthnBtn) {
            if (method === 'zkteco') {
                methodZkBtn.classList.add('bg-white', 'shadow-sm', 'text-blue-600');
                methodZkBtn.classList.remove('text-gray-500', 'hover:text-gray-700');

                methodWebAuthnBtn.classList.remove('bg-white', 'shadow-sm', 'text-blue-600');
                methodWebAuthnBtn.classList.add('text-gray-500', 'hover:text-gray-700');
            } else {
                methodWebAuthnBtn.classList.add('bg-white', 'shadow-sm', 'text-blue-600');
                methodWebAuthnBtn.classList.remove('text-gray-500', 'hover:text-gray-700');

                methodZkBtn.classList.remove('bg-white', 'shadow-sm', 'text-blue-600');
                methodZkBtn.classList.add('text-gray-500', 'hover:text-gray-700');
            }
        }
    }

    if (methodZkBtn) methodZkBtn.addEventListener('click', () => updateFingerprintMethodUI('zkteco'));
    if (methodWebAuthnBtn) methodWebAuthnBtn.addEventListener('click', () => updateFingerprintMethodUI('webauthn'));

    // Initialize Method UI
    updateFingerprintMethodUI('zkteco');

    // Initialize
    switchMethod('facial');

    // Cleanup
    window.addEventListener('beforeunload', stopCamera);

    // --- FACIAL RECOGNITION LOGIC ---

    async function startCamera() {
        if (stream) return; // Already running
        if (!elements.video) return;

        try {
            if (elements.statusText) elements.statusText.textContent = 'Requesting camera access...';

            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    facingMode: 'user',
                },
                audio: false,
            });

            elements.video.srcObject = stream;
            await elements.video.play();

            if (elements.statusText) elements.statusText.textContent = 'Camera ready. Hold still while we auto-capture...';
            startAutoCapture();
        } catch (error) {
            console.error('Camera error:', error);
            showFacialError('Camera access denied or unavailable. Please allow access and refresh.');
        }
    }

    let livenessDetector = null;

    function stopCamera() {
        if (captureInterval) {
            clearInterval(captureInterval);
            captureInterval = null;
        }
        if (livenessDetector) {
            livenessDetector.stop();
        }
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        if (elements.video) {
            elements.video.srcObject = null;
        }
        if (elements.statusMessage) {
            elements.statusMessage.classList.add('hidden');
        }
    }

    function startAutoCapture() {
        if (typeof window.LivenessDetector !== 'undefined') {
            if (!livenessDetector) {
                livenessDetector = new window.LivenessDetector();
                livenessDetector.onStatusChange = (msg) => {
                    if (elements.statusText) elements.statusText.textContent = msg;
                };
                
                livenessDetector.onBlinkDetected = async (boundingBox) => {
                    if (isProcessing) return;
                    await captureAndSendFrame(boundingBox);
                };
            }
            livenessDetector.init(elements.video).catch(err => {
                console.error("Liveness detector init failed, falling back to interval.", err);
                startLegacyInterval();
            });
        } else {
            startLegacyInterval();
        }
    }

    function startLegacyInterval() {
        if (captureInterval) clearInterval(captureInterval);

        captureInterval = setInterval(async () => {
            if (isProcessing) return;
            if (!stream || !elements.video || elements.video.videoWidth === 0) return;
            await captureAndSendFrame();
        }, CAPTURE_INTERVAL_MS);
    }

    async function captureAndSendFrame(boundingBox = null) {
        if (!elements.canvas || !elements.video) return;

        isProcessing = true;
        if (elements.processingMessage) {
            elements.processingMessage.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Analyzing face... this may take up to 30 seconds...';
            elements.processingMessage.classList.remove('hidden');
        }
        if (elements.statusMessage) elements.statusMessage.classList.add('hidden');
        if (elements.facialSuccess) elements.facialSuccess.classList.add('hidden');
        if (elements.facialError) elements.facialError.classList.add('hidden');

        if (elements.progressContainer && elements.progressBar) {
            elements.progressContainer.classList.remove('opacity-0');
            elements.progressContainer.classList.add('opacity-100');
            elements.progressBar.style.transitionDuration = '700ms';
            elements.progressBar.style.width = '0%';
            
            setTimeout(() => {
                elements.progressBar.style.width = '85%';
            }, 50);
        }

        const vWidthOriginal = elements.video.videoWidth;
        const vHeightOriginal = elements.video.videoHeight;
        
        const MAX_DIMENSION = 600;
        let scale = 1;
        if (Math.max(vWidthOriginal, vHeightOriginal) > MAX_DIMENSION) {
            scale = MAX_DIMENSION / Math.max(vWidthOriginal, vHeightOriginal);
        }
        
        const vWidth = vWidthOriginal * scale;
        const vHeight = vHeightOriginal * scale;
        
        elements.canvas.width = vWidth;
        elements.canvas.height = vHeight;
        const ctx = elements.canvas.getContext('2d');
        ctx.drawImage(elements.video, 0, 0, vWidth, vHeight);
        
        let imageData;
        if (boundingBox) {
            const padX = (boundingBox.maxX - boundingBox.minX) * 0.15;
            const padY = (boundingBox.maxY - boundingBox.minY) * 0.15;
            
            const startX = Math.max(0, (boundingBox.minX - padX) * vWidth);
            const startY = Math.max(0, (boundingBox.minY - padY) * vHeight);
            const cropW = Math.min(vWidth - startX, (boundingBox.maxX - boundingBox.minX + 2 * padX) * vWidth);
            const cropH = Math.min(vHeight - startY, (boundingBox.maxY - boundingBox.minY + 2 * padY) * vHeight);
            
            const cropCanvas = document.createElement('canvas');
            cropCanvas.width = cropW;
            cropCanvas.height = cropH;
            cropCanvas.getContext('2d').drawImage(
                elements.canvas, 
                startX, startY, cropW, cropH, 
                0, 0, cropW, cropH
            );
            imageData = cropCanvas.toDataURL('image/jpeg', 0.9);
        } else {
            imageData = elements.canvas.toDataURL('image/jpeg', 0.9);
        }

        try {
            const response = await fetch(window.routes.facialEnroll, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ 
                    facial_image: imageData,
                    consent_granted: elements.biometricConsent ? elements.biometricConsent.checked : false,
                    policy_version: '1.0.0'
                }),
            });

            const responseText = await response.text();
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                console.error('Non-JSON response:', responseText);
                throw new Error('Server returned invalid response. Please check console for details.');
            }

            if (!response.ok || !result.success) {
                // Special handling for 'No face detected' - don't stop the process, just let it retry
                if (result.error && result.error.includes('No face detected')) {
                    if (elements.statusText) elements.statusText.textContent = 'Face not detected. Please ensure you are visible and center.';
                    if (elements.statusMessage) elements.statusMessage.classList.remove('hidden');
                    
                    if (elements.progressContainer && elements.progressBar) {
                        elements.progressContainer.classList.remove('opacity-100');
                        elements.progressContainer.classList.add('opacity-0');
                        setTimeout(() => {
                            elements.progressBar.style.width = '0%';
                        }, 300);
                    }
                    return; // Don't throw, just exit and wait for next interval
                }
                throw new Error(result.error || 'Facial enrollment failed');
            }

            handleFacialSuccess();
        } catch (error) {
            console.error('Facial enrollment error:', error);

            let userMessage = error.message;
            if (userMessage.includes('timeout') || userMessage.includes('taking too long')) {
                userMessage = 'Processing took too long. Please ensure you are in good lighting and try again.';
            } else if (userMessage.includes('Failed to fetch') || userMessage.includes('Network')) {
                userMessage = 'Network error. Please try again.';
            }

            showFacialError(userMessage);
        } finally {
            isProcessing = false;
            if (elements.processingMessage) elements.processingMessage.classList.add('hidden');
        }
    }

    function handleFacialSuccess() {
        if (elements.progressBar) {
            elements.progressBar.style.transitionDuration = '300ms';
            elements.progressBar.style.width = '100%';
        }

        setTimeout(() => {
            if (elements.facialSuccess) elements.facialSuccess.classList.remove('hidden');
            if (elements.statusMessage) elements.statusMessage.classList.add('hidden');
            if (elements.cameraContainer) elements.cameraContainer.classList.add('hidden');
            stopCamera();

            // Hide facial section and show choice modal
            setTimeout(() => {
                if (elements.facialSection) elements.facialSection.classList.add('hidden');
                const choiceModal = document.getElementById('enrollment-choice');
                if (choiceModal) {
                    choiceModal.classList.remove('hidden');
                } else {
                    // Fallback if modal missing
                    window.location.href = window.routes.complete;
                }
            }, 1500);
        }, 400); // Give the progress bar time to reach 100%
    }

    // --- CHOICE MODAL LISTENERS ---
    const btnChoiceFingerprint = document.getElementById('btn-choice-fingerprint');
    const btnChoiceSkip = document.getElementById('btn-choice-skip');

    if (btnChoiceFingerprint) {
        btnChoiceFingerprint.addEventListener('click', () => {
            document.getElementById('enrollment-choice').classList.add('hidden');
            switchMethod('fingerprint');

            // Auto-start scanning (wait briefly for UI transition)
            setTimeout(() => {
                startFingerprintEnrollment();
            }, 500);
        });
    }

    if (btnChoiceSkip) {
        btnChoiceSkip.addEventListener('click', () => {
            window.location.href = window.routes.complete;
        });
    }

    function showFacialError(message) {
        stopCamera(); // Stop trying if error
        if (elements.statusMessage) elements.statusMessage.classList.add('hidden');
        if (elements.cameraContainer) elements.cameraContainer.classList.add('hidden');
        if (elements.facialError) elements.facialError.classList.remove('hidden');
        if (elements.facialErrorText) elements.facialErrorText.textContent = message;
        
        if (elements.progressContainer && elements.progressBar) {
            elements.progressContainer.classList.remove('opacity-100');
            elements.progressContainer.classList.add('opacity-0');
            setTimeout(() => {
                elements.progressBar.style.width = '0%';
            }, 300);
        }
    }

    // --- FINGERPRINT LOGIC ---
    let zktecoHardwareAvailable = false;

    // Pre-detect hardware in the background so we don't block the user gesture token during click
    setTimeout(async () => {
        zktecoHardwareAvailable = await zkTecoService.checkService();
        if (zktecoHardwareAvailable) {
            console.log('ZKTeco Hardware pre-detected.');
        } else {
            console.log('ZKTeco Hardware not found in background scan.');
        }
    }, 500);

    async function startFingerprintEnrollment() {
        const btn = elements.startFingerprintBtn;
        btn.disabled = true;

        if (zktecoHardwareAvailable) {
            console.log('ZKTeco Hardware detected. Using USB Scanner.');
            await startZKTecoEnrollment();
        } else {
            console.log('ZKTeco Hardware not found. Falling back to WebAuthn.');
            await startWebAuthnEnrollment();
        }
    }

    async function startZKTecoEnrollment() {
        const btn = elements.startFingerprintBtn;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Initializing Agent...';

        if (elements.fingerprintError) elements.fingerprintError.classList.add('hidden');
        if (elements.fingerprintSuccess) elements.fingerprintSuccess.classList.add('hidden');
        if (elements.fingerprintStatusMsg) elements.fingerprintStatusMsg.classList.add('hidden');

        try {
            if (elements.fingerprintStatusMsg) {
                elements.fingerprintStatusMsg.innerHTML = '<div class="text-sm p-3 bg-blue-50 text-blue-700 rounded-lg"><i class="fas fa-hand-pointer mr-2"></i> Please place your finger on the ZK Scanner...</div>';
                elements.fingerprintStatusMsg.classList.remove('hidden');
            }
            btn.innerHTML = '<i class="fas fa-fingerprint mr-2"></i> Scanning...';

            // Show pulsing scanner UI
            const pulsingIcon = document.getElementById('fingerprint-pulsing-icon');
            if (pulsingIcon) {
                pulsingIcon.classList.remove('hidden');
                pulsingIcon.classList.add('animate-pulse');
            }

            // --- PROCESS ---
            const onProgress = (index) => {
                // Not used in single-scan capture mode
            };

            // Capture
            const result = await zkTecoService.captureFingerprint(onProgress); // Returns { template, image }

            // Send to Server
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';

            const response = await fetch(window.routes.fingerprintEnroll, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    fingerprint_template: result.template,
                    device_id: 'zk9500_local_agent'
                })
            });

            const data = await response.json();
            if (!response.ok) throw new Error(data.error || 'Enrollment failed');

            if (pulsingIcon) {
                pulsingIcon.classList.remove('animate-pulse');
            }

            if (data) {
                // Success UI Update
                if (elements.fingerprintStatusMsg) elements.fingerprintStatusMsg.classList.add('hidden');
                if (elements.fingerprintSuccess) elements.fingerprintSuccess.classList.remove('hidden');
                btn.innerHTML = '<i class="fas fa-check mr-2"></i> Fingerprint Enrolled';
                btn.classList.add('bg-green-600', 'hover:bg-green-700');
            }

            setTimeout(() => {
                window.location.href = window.routes.complete;
            }, 1500);

        } catch (error) {
            if (document.getElementById('fingerprint-pulsing-icon')) {
                document.getElementById('fingerprint-pulsing-icon').classList.remove('animate-pulse');
            }
            if (elements.fingerprintError) {
                elements.fingerprintError.classList.remove('hidden');
                const errText = document.getElementById('fingerprint-error-text');
                if (errText) errText.textContent = error.message || 'Capture failed';
            }
            if (elements.fingerprintStatusMsg) elements.fingerprintStatusMsg.classList.add('hidden');

            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-fingerprint mr-2"></i> Retry Scan';
        }
    }

    // --- WebAuthn Logic (Fallback) ---
    async function startWebAuthnEnrollment() {
        const btn = elements.startFingerprintBtn;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Requesting Device Security...';

        if (elements.fingerprintError) elements.fingerprintError.classList.add('hidden');
        if (elements.fingerprintStatusMsg) {
            elements.fingerprintStatusMsg.innerHTML = '<div class="text-sm p-3 bg-blue-50 text-blue-700 rounded-lg"><i class="fas fa-mobile-alt mr-2"></i> Please use your device\'s built-in fingerprint scanner or Face ID...</div>';
            elements.fingerprintStatusMsg.classList.remove('hidden');
        }

        try {
            // 1. Get Options from Server
            const optionsResponse = await fetch(window.routes.registrationOptions);
            const data = await optionsResponse.json();

            if (data.error) throw new Error(data.error);

            // The lbuchs library wraps everything in a 'publicKey' property
            const options = data.publicKey || data;

            // 2. Format options for browser API
            options.challenge = base64ToArrayBuffer(options.challenge);
            options.user.id = base64ToArrayBuffer(options.user.id);
            if (options.excludeCredentials) {
                for (let cred of options.excludeCredentials) {
                    cred.id = base64ToArrayBuffer(cred.id);
                }
            }

            // 3. Trigger native prompt
            const credential = await navigator.credentials.create({ publicKey: options });

            // 4. Send response to server
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';

            const verifyResponse = await fetch(window.routes.verifyRegistration, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    id: credential.id,
                    rawId: arrayBufferToBase64(credential.rawId),
                    response: {
                        clientDataJSON: arrayBufferToBase64(credential.response.clientDataJSON),
                        attestationObject: arrayBufferToBase64(credential.response.attestationObject)
                    },
                    clientExtensionResults: credential.getClientExtensionResults()
                })
            });

            const result = await verifyResponse.json();

            if (!verifyResponse.ok) throw new Error(result.error || 'Verification failed on server.');

            // Success
            if (elements.fingerprintSuccess) elements.fingerprintSuccess.classList.remove('hidden');
            if (elements.fingerprintStatusMsg) elements.fingerprintStatusMsg.classList.add('hidden');
            btn.innerHTML = '<i class="fas fa-check mr-2"></i> Enrolled via Device';
            btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            btn.classList.add('bg-green-600', 'hover:bg-green-700');

            setTimeout(() => {
                window.location.href = window.routes.complete;
            }, 1500);

        } catch (error) {
            console.error('WebAuthn Error:', error);
            if (elements.fingerprintError) elements.fingerprintError.classList.remove('hidden');
            if (elements.fingerprintErrorText) elements.fingerprintErrorText.textContent = error.message.includes('The operation either timed out or was not allowed') ? 'Biometric verification cancelled.' : error.message;
            if (elements.fingerprintStatusMsg) elements.fingerprintStatusMsg.classList.add('hidden');

            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-fingerprint mr-2"></i> Retry Device Biometric';
        }
    }

    // --- HELPERS ---

    function base64ToArrayBuffer(base64) {
        // Convert base64url to standard base64
        base64 = base64.replace(/-/g, '+').replace(/_/g, '/');
        while (base64.length % 4) base64 += '=';

        const binary_string = window.atob(base64);
        const len = binary_string.length;
        const bytes = new Uint8Array(len);
        for (let i = 0; i < len; i++) {
            bytes[i] = binary_string.charCodeAt(i);
        }
        return bytes.buffer;
    }

    function arrayBufferToBase64(buffer) {
        let binary = '';
        const bytes = new Uint8Array(buffer);
        const len = bytes.byteLength;
        for (let i = 0; i < len; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return window.btoa(binary);
    }
});
