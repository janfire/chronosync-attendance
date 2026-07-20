<div class="facial-section" id="facial-section">
    <div id="consent-section" class="mb-4 p-4 border border-emerald-200 bg-emerald-50 rounded-lg text-sm text-left hidden">
        <label class="flex items-start space-x-3 cursor-pointer">
            <input type="checkbox" id="biometric-consent" class="mt-1 w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
            <span class="text-gray-700">
                I have read and agree to the <a href="{{ route('policy.biometric') }}" target="_blank" class="text-emerald-600 hover:underline font-semibold">Biometric Privacy Policy (v1.0.0)</a>. I consent to the collection, storage, and processing of my facial data for attendance verification.
            </span>
        </label>
    </div>

    <div class="camera-container relative mx-auto w-full max-w-sm hidden" id="camera-container">
        <video id="facial-video" autoplay playsinline muted class="w-full rounded-lg border border-gray-200 bg-black/50 aspect-[4/3] object-cover shadow-inner"></video>
        <canvas id="facial-canvas" class="hidden"></canvas>
        
        <!-- Premium Progress Bar -->
        <div id="facial-progress-container" class="absolute bottom-0 left-0 w-full h-1.5 bg-gray-900/30 rounded-b-lg overflow-hidden backdrop-blur-md opacity-0 transition-opacity duration-300">
            <div id="facial-progress-bar" class="h-full bg-gradient-to-r from-emerald-400 to-emerald-500 w-0 transition-[width] duration-700 ease-out shadow-[0_0_8px_rgba(52,211,153,0.8)]"></div>
        </div>
    </div>

    <div class="mt-3 sm:mt-4 space-y-2 sm:space-y-3">
        <div id="status-message" class="text-center p-2.5 sm:p-3 rounded-lg bg-emerald-50 border border-emerald-200">
            <p id="status-text" class="text-emerald-700 font-medium text-xs sm:text-sm">Initializing camera...</p>
        </div>

        <div id="processing-message" class="text-center hidden">
            <div class="animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-emerald-600 mx-auto mb-2"></div>
            <p class="text-gray-600 text-xs sm:text-sm">Capturing facial data...</p>
        </div>

        <div class="text-xs sm:text-sm text-gray-500 text-center mb-2">
            Hold still and ensure good lighting. The system will capture automatically.
        </div>

        <div class="bg-gray-50 border border-gray-100 rounded-lg p-2.5">
            <div class="flex flex-col sm:flex-row justify-center sm:space-x-4 items-center text-xs text-gray-500 gap-1.5 sm:gap-0">
                <div class="flex items-center"><i class="fas fa-lightbulb text-amber-500 mr-1.5"></i> Well-lit area</div>
                <div class="hidden sm:block text-gray-300">•</div>
                <div class="flex items-center"><i class="fas fa-expand text-blue-500 mr-1.5"></i> Center face</div>
                <div class="hidden sm:block text-gray-300">•</div>
                <div class="flex items-center"><i class="fas fa-glasses text-emerald-500 mr-1.5"></i> Remove glasses</div>
            </div>
        </div>
    </div>

    <div id="facial-success" class="mt-3 sm:mt-4 p-2.5 sm:p-3 bg-green-100 border border-green-200 rounded-lg text-center hidden">
        <i class="fas fa-check-circle text-green-500 text-xl sm:text-2xl mb-2"></i>
        <p class="text-green-700 font-semibold text-xs sm:text-sm">Facial data enrolled successfully!</p>
        <p class="text-green-600 text-xs">You will be redirected shortly...</p>
    </div>

    <div id="facial-error" class="mt-3 sm:mt-4 p-2.5 sm:p-3 bg-red-100 border border-red-200 rounded-lg text-center hidden">
        <i class="fas fa-exclamation-triangle text-red-500 text-xl sm:text-2xl mb-2"></i>
        <p id="error-text" class="text-red-700 font-semibold text-xs sm:text-sm"></p>
        <button id="retry-facial" class="mt-2 bg-red-600 hover:bg-red-700 active:bg-red-800 active:scale-[0.98] text-white px-4 py-2 rounded-lg text-xs sm:text-sm touch-manipulation transition duration-200">
            <i class="fas fa-redo mr-1"></i>Retry
        </button>
    </div>
</div>




