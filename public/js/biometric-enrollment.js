// Biometric Enrollment JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const elements = {
        fingerprintSection: document.getElementById('fingerprint-section'),
        facialSection: document.getElementById('facial-section'),
        completionSection: document.getElementById('completion-section'),
        startFingerprintBtn: document.getElementById('start-fingerprint-scan'),
        startFacialBtn: document.getElementById('start-facial-scan'),
        fingerprintProgressBar: document.getElementById('fingerprint-progress-bar'),
        fingerprintProgressText: document.getElementById('fingerprint-progress-text'),
        facialProgressBar: document.getElementById('facial-progress-bar'),
        facialProgressText: document.getElementById('facial-progress-text'),
        fingerprintSuccess: document.getElementById('fingerprint-success'),
        facialSuccess: document.getElementById('facial-success'),
        fingerprintStep: document.getElementById('fingerprint-step'),
        faceStep: document.getElementById('face-step'),
        progressText: document.getElementById('progress-text')
    };

    let fingerprintCompleted = false;
    let facialCompleted = false;

    // Initialize event listeners
    initializeEventListeners();

    // Check existing enrollment status
    checkEnrollmentStatus();

    function initializeEventListeners() {
        if (elements.startFingerprintBtn) {
            elements.startFingerprintBtn.addEventListener('click', startFingerprintScan);
        }

        if (elements.startFacialBtn) {
            elements.startFacialBtn.addEventListener('click', startFacialScan);
        }
    }

    function startFingerprintScan() {
        let progress = 0;
        elements.startFingerprintBtn.disabled = true;
        elements.startFingerprintBtn.innerHTML = '<i class="fas fa-sync-alt animate-spin mr-2"></i>Scanning...';
        
        const interval = setInterval(() => {
            progress += 2;
            elements.fingerprintProgressBar.style.width = progress + '%';
            elements.fingerprintProgressText.textContent = progress + '%';
            
            if (progress >= 100) {
                clearInterval(interval);
                storeFingerprintData();
            }
        }, 50);
    }

    function startFacialScan() {
        let progress = 0;
        elements.startFacialBtn.disabled = true;
        elements.startFacialBtn.innerHTML = '<i class="fas fa-sync-alt animate-spin mr-2"></i>Capturing...';
        
        const interval = setInterval(() => {
            progress += 2;
            elements.facialProgressBar.style.width = progress + '%';
            elements.facialProgressText.textContent = progress + '%';
            
            if (progress >= 100) {
                clearInterval(interval);
                storeFacialData();
            }
        }, 50);
    }

    function storeFingerprintData() {
        fetch('{{ route("biometric.store.fingerprint") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                fingerprint_data: 'simulated_fingerprint_template_' + Date.now()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                handleFingerprintSuccess();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            handleFingerprintError();
        });
    }

    function storeFacialData() {
        fetch('{{ route("biometric.store.facial") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                facial_data: 'simulated_facial_template_' + Date.now()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                handleFacialSuccess();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            handleFacialError();
        });
    }

    function handleFingerprintSuccess() {
        elements.fingerprintSuccess.classList.remove('hidden');
        fingerprintCompleted = true;
        elements.fingerprintStep.classList.remove('bg-yellow-500');
        elements.fingerprintStep.classList.add('bg-green-500');
        
        // Enable facial recognition
        elements.facialSection.classList.remove('opacity-50');
        elements.startFacialBtn.disabled = false;
        elements.startFacialBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
        elements.startFacialBtn.classList.add('bg-purple-600', 'hover:bg-purple-700');
        elements.startFacialBtn.innerHTML = '<i class="fas fa-camera mr-2"></i>Start Facial Capture';
        
        elements.progressText.textContent = 'Step 2 of 2';
        elements.faceStep.classList.remove('bg-gray-400');
        elements.faceStep.classList.add('bg-yellow-500');
    }

    function handleFacialSuccess() {
        elements.facialSuccess.classList.remove('hidden');
        facialCompleted = true;
        elements.faceStep.classList.remove('bg-yellow-500');
        elements.faceStep.classList.add('bg-green-500');
        
        // Show completion section
        setTimeout(() => {
            elements.fingerprintSection.classList.add('hidden');
            elements.facialSection.classList.add('hidden');
            elements.completionSection.classList.remove('hidden');
        }, 1500);
    }

    function handleFingerprintError() {
        alert('Error storing fingerprint data. Please try again.');
        elements.startFingerprintBtn.disabled = false;
        elements.startFingerprintBtn.innerHTML = '<i class="fas fa-play-circle mr-2"></i>Retry Fingerprint Scan';
        elements.fingerprintProgressBar.style.width = '0%';
        elements.fingerprintProgressText.textContent = '0%';
    }

    function handleFacialError() {
        alert('Error storing facial data. Please try again.');
        elements.startFacialBtn.disabled = false;
        elements.startFacialBtn.innerHTML = '<i class="fas fa-camera mr-2"></i>Retry Facial Capture';
        elements.facialProgressBar.style.width = '0%';
        elements.facialProgressText.textContent = '0%';
    }

    function checkEnrollmentStatus() {
        // Route URL should be passed from Blade template, but handle gracefully if not
        const statusUrl = window.biometricStatusUrl || '/biometric/status';
        fetch(statusUrl)
            .then(response => response.json())
            .then(data => {
                if (data.fingerprint_enrolled) {
                    handleExistingFingerprint();
                }
                
                if (data.facial_enrolled) {
                    handleExistingFacial();
                }
            })
            .catch(error => {
                console.error('Error checking enrollment status:', error);
            });
    }

    function handleExistingFingerprint() {
        fingerprintCompleted = true;
        elements.fingerprintSuccess.classList.remove('hidden');
        elements.fingerprintStep.classList.add('bg-green-500');
        elements.facialSection.classList.remove('opacity-50');
        elements.startFacialBtn.disabled = false;
        elements.startFacialBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
        elements.startFacialBtn.classList.add('bg-purple-600', 'hover:bg-purple-700');
        elements.startFacialBtn.innerHTML = '<i class="fas fa-camera mr-2"></i>Start Facial Capture';
        elements.faceStep.classList.remove('bg-gray-400');
        elements.faceStep.classList.add('bg-yellow-500');
        elements.progressText.textContent = 'Step 2 of 2';
    }

    function handleExistingFacial() {
        facialCompleted = true;
        elements.facialSuccess.classList.remove('hidden');
        elements.faceStep.classList.remove('bg-yellow-500');
        elements.faceStep.classList.add('bg-green-500');
        
        if (fingerprintCompleted) {
            elements.fingerprintSection.classList.add('hidden');
            elements.facialSection.classList.add('hidden');
            elements.completionSection.classList.remove('hidden');
        }
    }
});