<div class="fingerprint-section hidden" id="fingerprint-section">
    <div class="p-6 bg-white rounded-lg border border-gray-200 text-center">
            <!-- 1-Step Visualizer -->
            <div class="flex justify-center items-center mb-6 mt-4">
                <div id="fingerprint-pulsing-icon" class="hidden w-24 h-24 rounded-full bg-emerald-50 border-4 border-emerald-500 flex items-center justify-center shadow-[0_0_15px_rgba(16,185,129,0.5)]">
                    <i class="fas fa-fingerprint text-4xl text-emerald-600"></i>
                </div>
            </div>

            <h3 class="text-lg font-semibold text-gray-800" id="fingerprint-instruction-title">Fingerprint Enrollment</h3>
            <p class="text-gray-500 text-sm mt-1" id="fingerprint-instruction-text">Please place your finger firmly on the scanner.</p>
            
            <div class="mt-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                    <i class="fas fa-link mr-1"></i> ZKTeco Agent Active
                </span>
            </div>
        </div>

        <div id="fingerprint-status-message" class="mb-4">
            <div class="text-sm p-3 bg-emerald-50 text-emerald-700 rounded-lg border border-emerald-100">
                Ready to scan. Please follow your device's instructions.
            </div>
        </div>

        <div id="fingerprint-success" class="hidden mb-4">
            <div class="text-sm p-3 bg-green-50 text-green-700 rounded-lg border border-green-100 flex items-center justify-center">
                <i class="fas fa-check-circle mr-2"></i> Fingerprint captured successfully!
            </div>
        </div>

        <div id="fingerprint-error" class="hidden mb-4">
            <div class="text-sm p-3 bg-red-50 text-red-700 rounded-lg border border-red-100 flex items-center justify-center">
                <i class="fas fa-exclamation-circle mr-2"></i> <span id="fingerprint-error-text">Scan failed</span>
            </div>
        </div>

        <button id="start-fingerprint-scan" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-6 rounded-lg transition-colors flex items-center justify-center mx-auto">
            <i class="fas fa-fingerprint mr-2"></i> Scan Fingerprint
        </button>
    </div>
</div>


