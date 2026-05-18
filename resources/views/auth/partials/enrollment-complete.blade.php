<div id="completion-section" class="hidden">
    <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-xl p-4 sm:p-6 border-2 border-green-200 text-center">
        <div class="text-green-500 text-4xl sm:text-5xl lg:text-6xl mb-3 sm:mb-4">
            <i class="fas fa-check-circle"></i>
        </div>
        <h3 class="text-xl sm:text-2xl font-bold text-gray-800 mb-2">Enrollment Complete!</h3>
        <p class="text-gray-600 mb-4 sm:mb-6 text-xs sm:text-sm">Your biometric data has been successfully enrolled and secured.</p>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-4 sm:mb-6">
            <div class="bg-white p-3 sm:p-4 rounded-lg border border-green-200">
                <i class="fas fa-fingerprint text-xl sm:text-2xl text-emerald-500 mb-2"></i>
                <p class="font-semibold text-xs sm:text-sm">Fingerprint</p>
                <p class="text-xs sm:text-sm text-green-600">✓ Successfully enrolled</p>
            </div>
            <div class="bg-white p-3 sm:p-4 rounded-lg border border-green-200">
                <i class="fas fa-user-circle text-xl sm:text-2xl text-purple-500 mb-2"></i>
                <p class="font-semibold text-xs sm:text-sm">Facial Recognition</p>
                <p class="text-xs sm:text-sm text-green-600">✓ Successfully enrolled</p>
            </div>
        </div>
        
        <a href="{{ route('biometric.complete') }}" 
           class="inline-block bg-green-600 hover:bg-green-700 active:bg-green-800 active:scale-[0.98] text-white font-semibold py-2.5 sm:py-3 px-6 sm:px-8 rounded-lg transition duration-200 transform touch-manipulation text-xs sm:text-sm">
            <i class="fas fa-arrow-right mr-2"></i>Complete Registration
        </a>
    </div>
</div>

