import ZKTecoService from './zkteco-service.js';

document.addEventListener('DOMContentLoaded', function () {
    const elements = {
        video: document.getElementById('attendance-video'),
        canvas: document.getElementById('attendance-canvas'),

        // Status Indicators
        zkIndicator: document.getElementById('fingerprint-indicator'),
        zkDot: document.getElementById('zk-status-dot'),
        zkText: document.getElementById('zk-status-text'),
        zkIcon: document.getElementById('zk-icon'),

        // Overlays
        processingOverlay: document.getElementById('processing-overlay'),
        resultOverlay: document.getElementById('result-overlay'),
        resultTitle: document.getElementById('result-title'),
        resultMessage: document.getElementById('result-message'),
        resultIconContainer: document.getElementById('result-icon-container'),

        clockStatusTitle: document.getElementById('clock-status-title')
    };

    let stream = null;
    let isProcessing = false;
    let faceDetectionInterval = null;
    let isClockedIn = false; // Prevent double clocking

    // Services
    const zkTecoService = new ZKTecoService();

    // Initialize
    console.log("Attendance Script Loaded - Manual Start Mode");

    // Auto-start is explicitly disabled.
    // manualStartOnly(); 

    // --- SELECTION LOGIC ---
    
    const selectionScreen = document.getElementById('selection-screen');
    const cameraContainer = document.getElementById('camera-container');
    const fingerprintContainer = document.getElementById('fingerprint-container');
    const backButton = document.getElementById('back-to-selection');
    const activeHeader = document.getElementById('active-header');

    if (document.getElementById('btn-select-face')) {
        document.getElementById('btn-select-face').addEventListener('click', () => startFaceMode());
    }
    if (document.getElementById('btn-select-finger')) {
        document.getElementById('btn-select-finger').addEventListener('click', () => startFingerMode());
    }
    if (backButton) {
        backButton.addEventListener('click', () => resetToSelection());
    }

    async function startFaceMode() {
        if (selectionScreen) selectionScreen.classList.add('hidden');
        if (cameraContainer) cameraContainer.classList.add('visible');
        if (activeHeader) activeHeader.classList.add('visible');
        
        const titleEl = document.getElementById('active-method-title');
        if (titleEl) titleEl.textContent = "Facial Scan";

        // Face mode: Start Camera AND Fingerprint (Hybrid convenience)
        await startCamera();

        // Show small indicator
        if (elements.zkIndicator) elements.zkIndicator.classList.add('visible');
        initZKTeco();
    }

    async function startFingerMode() {
        if (selectionScreen) selectionScreen.classList.add('hidden');
        if (fingerprintContainer) fingerprintContainer.classList.add('visible');
        if (activeHeader) activeHeader.classList.add('visible');

        const titleEl = document.getElementById('active-method-title');
        if (titleEl) titleEl.textContent = "Fingerprint Scan";

        // Fingerprint mode: Show large indicator logic if needed, 
        // but here we just show the prompt and start ZK

        // We might want to show the bottom indicator too?
        if (elements.zkIndicator) elements.zkIndicator.classList.add('visible');

        // DO NOT start camera
        initZKTeco();
    }

    function resetToSelection() {
        // Stop Camera
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        if (faceDetectionInterval) {
            clearInterval(faceDetectionInterval);
            faceDetectionInterval = null;
        }
        isProcessing = false;

        // Reset UI
        if (selectionScreen) selectionScreen.classList.remove('hidden');
        if (cameraContainer) cameraContainer.classList.remove('visible');
        if (fingerprintContainer) fingerprintContainer.classList.remove('visible');
        if (activeHeader) activeHeader.classList.remove('visible');
        if (elements.zkIndicator) elements.zkIndicator.classList.remove('visible');
        
        // Hide result and processing if open
        hideResult();
        if (elements.processingOverlay) elements.processingOverlay.style.display = 'none';
    }

    // Legacy init function, renamed to ensure it's not called
    async function unused_initializeSystem() {
        // Kept for reference but not called automatically
        await startCamera();
        initZKTeco();
    }

    // --- FACIAL RECOGNITION ---

    async function startCamera() {
        if (!elements.video) return;

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { width: 640, height: 480, facingMode: 'user' }
            });
            elements.video.srcObject = stream;

            elements.video.onloadedmetadata = () => {
                elements.video.play().then(() => startFaceDetection());
            };
        } catch (err) {
            console.error('Camera error:', err);
            showResult('error', 'Camera Error', 'Could not access camera. Please allow permissions.');
        }
    }

    let livenessDetector = null;

    function startFaceDetection() {
        if (typeof window.LivenessDetector !== 'undefined') {
            if (!livenessDetector) {
                livenessDetector = new window.LivenessDetector();
                livenessDetector.onStatusChange = (msg) => {
                    const badgeContainer = document.querySelector('.cam-status-badge');
                    if (badgeContainer) {
                        const badgeText = badgeContainer.querySelector('span');
                        if (badgeText) badgeText.textContent = msg;
                    }
                };
                
                livenessDetector.onBlinkDetected = async () => {
                    if (isProcessing || isClockedIn) return;
                    await captureAndVerify();
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
        if (faceDetectionInterval) clearInterval(faceDetectionInterval);
        faceDetectionInterval = setInterval(async () => {
            if (isProcessing || isClockedIn) return;
            await captureAndVerify();
        }, 3000);
    }

    async function captureAndVerify() {
        // Capture frame
        const ctx = elements.canvas.getContext('2d');
        elements.canvas.width = elements.video.videoWidth;
        elements.canvas.height = elements.video.videoHeight;
        ctx.drawImage(elements.video, 0, 0);

        const imageData = elements.canvas.toDataURL('image/jpeg', 0.8);

        isProcessing = true;
        let result; // Declare outside try so finally block can access it
        try {
            const position = await getCurrentPosition();

            const badgeContainer = document.querySelector('.cam-status-badge');
            if (badgeContainer) {
                const badgeText = badgeContainer.querySelector('span');
                if (badgeText) badgeText.textContent = "Processing data...";
            }

            const response = await fetch('/attendance/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({
                    facial_image: imageData,
                    latitude: position?.latitude,
                    longitude: position?.longitude,
                    accuracy: position?.accuracy
                })
            });

            result = await response.json();

            if (result.success) {
                handleSuccess(result);
                if (badgeContainer) {
                    const badgeText = badgeContainer.querySelector('span');
                    if (badgeText) badgeText.textContent = "Face Verified!";
                    const dot = badgeContainer.querySelector('.dot');
                    if (dot) dot.style.background = 'var(--success)';
                }
            } else {
                // Check for registration prompt
                if (result.action_required === 'registration_prompt') {
                    // Pause detection while alert is open
                    if (faceDetectionInterval) clearInterval(faceDetectionInterval);
                    if (livenessDetector) livenessDetector.stop();

                    // Use SweetAlert2 if available, otherwise fallback to confirm
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Not Recognized',
                            text: result.message || 'Face not found in our records. Would you like to register?',
                            icon: 'warning',
                            showCancelButton: true,
                            showDenyButton: true,
                            confirmButtonColor: '#2563eb',
                            denyButtonColor: '#0ea5e9',
                            cancelButtonColor: '#ef4444',
                            confirmButtonText: '<i class="fas fa-user-plus mr-1"></i> Yes, Register Now',
                            denyButtonText: '<i class="fas fa-user-pen mr-1"></i> Update Biometrics',
                            cancelButtonText: 'Cancel',
                            allowOutsideClick: false
                        }).then((swalResult) => {
                            if (swalResult.isConfirmed) {
                                window.location.href = '/register';
                            } else if (swalResult.isDenied) {
                                Swal.fire({
                                    title: 'Update Biometrics',
                                    html: `
                                        <div style="text-align:left">
                                            <label style="display:block;margin-bottom:6px;font-weight:600;">Email or Employee Number</label>
                                            <input id="update-identifier" class="swal2-input" placeholder="e.g. name@zou.ac.zw or EMP001" style="margin:0 0 10px 0;" />
                                            <label style="display:block;margin-bottom:6px;font-weight:600;">Password</label>
                                            <input id="update-password" type="password" class="swal2-input" placeholder="Your password" style="margin:0;" />
                                        </div>
                                    `,
                                    icon: 'info',
                                    showCancelButton: true,
                                    confirmButtonText: 'Continue',
                                    cancelButtonText: 'Cancel',
                                    confirmButtonColor: '#2563eb',
                                    allowOutsideClick: false,
                                    preConfirm: async () => {
                                        const identifier = document.getElementById('update-identifier')?.value?.trim();
                                        const password = document.getElementById('update-password')?.value ?? '';

                                        if (!identifier || !password) {
                                            Swal.showValidationMessage('Please enter your email/employee number and password.');
                                            return;
                                        }

                                        try {
                                            const res = await fetch('/biometric/update-login', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': getCsrfToken()
                                                },
                                                body: JSON.stringify({ identifier, password })
                                            });

                                            const data = await res.json();
                                            if (!res.ok) {
                                                throw new Error(data.message || 'Login failed');
                                            }
                                            return data;
                                        } catch (error) {
                                            Swal.showValidationMessage(`Request failed: ${error}`);
                                        }
                                    }
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = '/biometric/enrollment';
                                    } else {
                                        // Resume detection if they cancel update
                                        startFaceDetection();
                                    }
                                });
                            } else {
                                // Resume detection if they click Cancel
                                startFaceDetection();
                            }
                            const badgeText = badgeContainer.querySelector('span');
                            if (badgeText) badgeText.textContent = "Face not recognized.";
                            const dot = badgeContainer.querySelector('.dot');
                            if (dot) dot.style.background = 'var(--error)';
                        }
                    } else {
                        // Handle other errors - extract error message from various possible locations
                        console.error('=== FACIAL VERIFICATION ERROR ===');
                        console.error('Full response object:', result);
                        console.error('Response JSON:', JSON.stringify(result, null, 2));
                        console.error('result.error:', result.error);
                        console.error('result.message:', result.message);
                        console.error('result.errors?.message:', result.errors?.message);
                        const errorMsg = result.error || result.message || result.errors?.message || 'Verification failed. Please try again.';
                        console.log('Extracted error message:', errorMsg);
                        console.log('errorMsg type:', typeof errorMsg);
                        console.log('errorMsg is empty:', errorMsg === 'Verification failed. Please try again.');
                        
                        if (badgeContainer) {
                            const badgeText = badgeContainer.querySelector('span');
                            if (badgeText) badgeText.textContent = "Verification failed.";
                            const dot = badgeContainer.querySelector('.dot');
                            if (dot) dot.style.background = 'var(--error)';
                        }
                        
                        // Pause detection and show error
                        clearInterval(faceDetectionInterval);
                        isProcessing = false;
                        
                        if (typeof Swal !== 'undefined') {
                            console.log('Showing Swal with error:', errorMsg);
                            Swal.fire({
                                title: 'Cannot Clock In',
                                text: errorMsg,
                                icon: 'error',
                                confirmButtonColor: '#ef4444',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false,
                                didOpen: () => console.log('Swal dialog opened')
                            }).then(() => {
                                console.log('User clicked OK, resuming detection');
                                isProcessing = false;
                                startFaceDetection();
                            });
                        } else {
                            console.warn('SweetAlert2 not available, using alert()');
                            alert(errorMsg);
                            setTimeout(() => {
                                startFaceDetection();
                            }, 1000);
                        }
                    }
                }
            } catch (e) {
                console.error('Face verification error', e);
            } finally {
                // Only reset isProcessing if we're not showing a modal
                if (!isClockedIn && (!window.Swal || !Swal.isVisible())) {
                    // Don't reset immediately if we're in an error state that shows a message
                    if (!result || result.success === false && result.action_required !== 'registration_prompt') {
                        // Already handled with setTimeout above
                    } else {
                        isProcessing = false;
                    }
                }

                // Reset the badge if not clocked in and no modal showing
                if (!isClockedIn && (!window.Swal || !Swal.isVisible())) {
                    const badgeContainer = document.querySelector('.cam-status-badge');
                    if (badgeContainer) {
                        const badgeText = badgeContainer.querySelector('span');
                        if (badgeText && badgeText.textContent !== "Face not recognized." && badgeText.textContent !== "Verification failed.") {
                            badgeText.textContent = "Looking for face...";
                        }
                        const dot = badgeContainer.querySelector('.dot');
                        if (dot && dot.style.background !== 'var(--error)') {
                            dot.style.background = 'var(--cyan)';
                        }
                    }
                }
            }
        }, 3000); // Check every 3 seconds
    }

    // --- FINGERPRINT RECOGNITION ---

    async function initZKTeco() {
        updateZKStatus('connecting');

        try {
            const connected = await zkTecoService.checkService();
            if (connected) {
                updateZKStatus('ready');
                startZKScanLoop();
            } else {
                updateZKStatus('offline');
            }
        } catch (e) {
            updateZKStatus('offline');
        }
    }

    async function startZKScanLoop() {
        while (!isClockedIn) {
            if (isProcessing) {
                await new Promise(r => setTimeout(r, 1000)); // Wait if face is processing
                continue;
            }

            let result; // Declare outside try so we can check it
            try {
                // identifyFingerprint waits up to 10s for a finger
                updateZKStatus('scanning');
                const capture = await zkTecoService.identifyFingerprint();

                // If we get here, we have a template!
                isProcessing = true;
                updateZKStatus('processing');

                const position = await getCurrentPosition();

                const response = await fetch('/attendance/verify-fingerprint', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        fingerprint_template: capture.template,
                        latitude: position?.latitude,
                        longitude: position?.longitude
                    })
                });

                result = await response.json();

                if (result.success) {
                    handleSuccess(result);
                    break;
                } else {
                    // Check for registration prompt
                    if (result.action_required === 'registration_prompt') {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Not Recognized',
                                text: result.message || 'Fingerprint not found. Would you like to register?',
                                icon: 'warning',
                                showCancelButton: true,
                                showDenyButton: true,
                                confirmButtonColor: '#2563eb',
                                denyButtonColor: '#0ea5e9',
                                cancelButtonColor: '#ef4444',
                                confirmButtonText: '<i class="fas fa-user-plus mr-1"></i> Yes, Register Now',
                                denyButtonText: '<i class="fas fa-user-pen mr-1"></i> Update Biometrics',
                                cancelButtonText: 'Cancel',
                                allowOutsideClick: false
                            }).then((swalResult) => {
                                if (swalResult.isConfirmed) {
                                    window.location.href = '/register';
                                } else if (swalResult.isDenied) {
                                    Swal.fire({
                                        title: 'Update Biometrics',
                                        html: `
                                            <div style="text-align:left">
                                                <label style="display:block;margin-bottom:6px;font-weight:600;">Email or Employee Number</label>
                                                <input id="update-identifier" class="swal2-input" placeholder="e.g. name@zou.ac.zw or EMP001" style="margin:0 0 10px 0;" />
                                                <label style="display:block;margin-bottom:6px;font-weight:600;">Password</label>
                                                <input id="update-password" type="password" class="swal2-input" placeholder="Your password" style="margin:0;" />
                                            </div>
                                        `,
                                        icon: 'info',
                                        showCancelButton: true,
                                        confirmButtonText: 'Continue',
                                        cancelButtonText: 'Cancel',
                                        confirmButtonColor: '#2563eb',
                                        allowOutsideClick: false,
                                        preConfirm: async () => {
                                            const identifier = document.getElementById('update-identifier')?.value?.trim();
                                            const password = document.getElementById('update-password')?.value ?? '';

                                            if (!identifier || !password) {
                                                Swal.showValidationMessage('Please enter your email/employee number and password.');
                                                return;
                                            }

                                            try {
                                                const res = await fetch('/biometric/update-login', {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'Accept': 'application/json',
                                                        'X-CSRF-TOKEN': getCsrfToken()
                                                    },
                                                    body: JSON.stringify({ identifier, password })
                                                });

                                                const json = await res.json().catch(() => ({}));
                                                if (!res.ok || !json.success) {
                                                    const msg = json.error || 'Authentication failed. Please check your details.';
                                                    Swal.showValidationMessage(msg);
                                                    return;
                                                }

                                                return json;
                                            } catch (e) {
                                                Swal.showValidationMessage('Network error. Please try again.');
                                                return;
                                            }
                                        }
                                    }).then((loginResult) => {
                                        if (loginResult.isConfirmed && loginResult.value?.redirect_url) {
                                            window.location.href = loginResult.value.redirect_url;
                                        }
                                    });
                                }
                            });
                        } else {
                            if (confirm(result.message || 'Fingerprint not found. Would you like to register now?')) {
                                window.location.href = '/register';
                            }
                        }
                    } else {
                        // Handle non-registration errors - extract error from various possible locations
                        console.error('=== FINGERPRINT VERIFICATION ERROR ===');
                        console.error('Full response:', result);
                        console.error('Response JSON:', JSON.stringify(result, null, 2));
                        console.error('result.error:', result.error);
                        console.error('result.message:', result.message);
                        console.error('result.errors?.message:', result.errors?.message);
                        const errorMsg = result.error || result.message || result.errors?.message || 'Verification failed. Please try again.';
                        console.log('Extracted fingerprint error:', errorMsg);
                        console.log('errorMsg type:', typeof errorMsg);
                        console.log('errorMsg is empty:', errorMsg === 'Verification failed. Please try again.');
                        
                        isProcessing = false;
                        
                        if (typeof Swal !== 'undefined') {
                            console.log('Showing Swal dialog with fingerprint error:', errorMsg);
                            Swal.fire({
                                title: 'Cannot Clock In',
                                text: errorMsg,
                                icon: 'error',
                                confirmButtonColor: '#ef4444',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false,
                                didOpen: () => console.log('Fingerprint error Swal opened')
                            }).then(() => {
                                console.log('User acknowledged fingerprint error');
                                updateZKStatus('ready');
                            });
                            return;
                        } else {
                            console.warn('SweetAlert2 not available for fingerprint, using alert()');
                            alert(errorMsg);
                            await new Promise(r => setTimeout(r, 500));
                            updateZKStatus('ready');
                        }
                    }
                }

            } catch (e) {
                // Timeout or error
                if (e.message && e.message.includes('timeout')) {
                    // Just loop again
                    updateZKStatus('ready');
                } else {
                    console.error('ZK Error:', e);
                    updateZKStatus('error');
                    await new Promise(r => setTimeout(r, 5000)); // Wait before retry
                    updateZKStatus('ready');
                }
            } finally {
                if (!isClockedIn) isProcessing = false;
            }
        }
    }

    function updateZKStatus(status) {
        const colors = {
            connecting: 'bg-yellow-400',
            ready: 'bg-green-500',
            scanning: 'bg-blue-500 animate-pulse',
            processing: 'bg-purple-500',
            offline: 'bg-red-500',
            error: 'bg-orange-500'
        };

        const texts = {
            connecting: 'Connecting to Scanner...',
            ready: 'Scanner Ready - Place Finger',
            scanning: 'Scanning...',
            processing: 'Verifying Fingerprint...',
            offline: 'Scanner Offline (Is Agent Running?)',
            error: 'Scanner Error'
        };

        // Reset classes
        elements.zkDot.className = `w-2 h-2 rounded-full mr-2 transition-colors ${colors[status] || 'bg-gray-400'}`;
        elements.zkText.textContent = texts[status];

        if (status === 'ready' || status === 'scanning') {
            elements.zkIndicator.classList.add('border-blue-200', 'bg-blue-50');
            elements.zkIcon.classList.add('text-blue-500');
        } else if (status === 'offline') {
            elements.zkIndicator.classList.add('border-red-200', 'bg-red-50');
            elements.zkIcon.classList.add('text-red-400');
        }
    }

    // --- COMMON HELPERS ---

    function handleSuccess(result) {
        isClockedIn = true;
        if (faceDetectionInterval) {
            clearInterval(faceDetectionInterval);
            faceDetectionInterval = null;
        }

        // Play sound
        const audio = new Audio('/sounds/success.mp3'); 
        audio.play().catch(e => { });

        // Extract message parts for display (support both merged and nested structures)
        const title = result.message ? result.message.split('!')[0] + '!' : 'Success!';
        const subtitle = result.message ? (result.message.split('!')[1] || '') : '';
        const userName = result.user_name || result.data?.user_name || '';
        const userDetail = userName ? `Logged in as ${userName}` : '';

        showResult('success', title, subtitle || userDetail);

        // Redirect URL (support both merged and nested structures)
        const redirectUrl = result.redirect_url || result.data?.redirect_url;

        // Redirect or Reload after 3 seconds
        setTimeout(() => {
            if (redirectUrl) {
                window.location.href = redirectUrl;
            } else {
                window.location.reload();
            }
        }, 3000);
    }

    function showResult(type, title, message) {
        if (elements.resultOverlay) elements.resultOverlay.classList.add('visible');
        if (elements.resultTitle) elements.resultTitle.textContent = title;
        if (elements.resultMessage) elements.resultMessage.textContent = message;

        if (elements.resultIconContainer) {
            if (type === 'success') {
                elements.resultIconContainer.innerHTML = '<i class="fas fa-check-circle text-6xl text-green-500"></i>';
            } else {
                elements.resultIconContainer.innerHTML = '<i class="fas fa-times-circle text-6xl text-red-500"></i>';
            }
        }
    }

    function hideResult() {
        if (elements.resultOverlay) elements.resultOverlay.classList.remove('visible');
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    async function getCurrentPosition() {
        if (!navigator.geolocation) return null;
        return new Promise(resolve => {
            navigator.geolocation.getCurrentPosition(
                pos => resolve({
                    latitude: pos.coords.latitude,
                    longitude: pos.coords.longitude,
                    accuracy: pos.coords.accuracy
                }),
                err => resolve(null),
                { timeout: 5000 }
            );
        });
    }
});
