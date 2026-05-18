<div class="facial-section" id="facial-section">
    <div class="camera-container relative">
        <video id="facial-video" autoplay playsinline muted class="w-full rounded-lg border border-gray-200 bg-black/50 max-h-[50vh] sm:max-h-none object-cover"></video>
        <canvas id="facial-canvas" class="hidden"></canvas>
    </div>

    <div class="mt-3 sm:mt-4 space-y-2 sm:space-y-3">
        <div id="status-message" class="text-center p-2.5 sm:p-3 rounded-lg bg-emerald-50 border border-emerald-200">
            <p id="status-text" class="text-emerald-700 font-medium text-xs sm:text-sm">Initializing camera...</p>
        </div>

        <div id="processing-message" class="text-center hidden">
            <div class="animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-emerald-600 mx-auto mb-2"></div>
            <p class="text-gray-600 text-xs sm:text-sm">Capturing facial data...</p>
        </div>

        <div class="text-xs sm:text-sm text-gray-500 text-center">
            Hold still and ensure good lighting. The system will capture automatically.
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




