@extends('layouts.guest')

@section('title', 'Biometric Privacy Policy - ChronoSync')

@section('content')
    <div class="max-w-3xl mx-auto py-12 px-6">
        <h1 class="text-3xl font-bold mb-6 text-emerald-600">Biometric Privacy Policy</h1>
        <p class="text-sm text-gray-500 mb-8">Effective Date: {{ date('F j, Y') }} | Version: 1.0.0</p>
        
        <div class="space-y-6 text-gray-700 leading-relaxed">
            <section>
                <h2 class="text-xl font-semibold mb-3">1. Information We Collect</h2>
                <p>To provide secure attendance tracking, ChronoSync collects and stores biometric identifiers and biometric information ("Biometric Data"). This includes mathematical representations of your facial geometry and/or fingerprint data.</p>
            </section>
            
            <section>
                <h2 class="text-xl font-semibold mb-3">2. Purpose of Collection</h2>
                <p>We collect this Biometric Data solely for the purpose of verifying your identity and tracking your attendance securely, preventing time fraud, and ensuring access control.</p>
            </section>
            
            <section>
                <h2 class="text-xl font-semibold mb-3">3. Disclosure and Sharing</h2>
                <p>We do not sell, lease, trade, or otherwise profit from your Biometric Data. Your data is encrypted and stored securely. We will not disclose or disseminate your Biometric Data to any third parties unless required by law, or with your explicit consent.</p>
            </section>
            
            <section>
                <h2 class="text-xl font-semibold mb-3">4. Data Retention and Destruction</h2>
                <p>We will retain your Biometric Data only until the initial purpose for collecting the data has been satisfied, or within three (3) years of your last interaction with the system, whichever occurs first. Upon reaching this timeframe, or upon your explicit request to revoke consent, your Biometric Data will be permanently destroyed from our systems.</p>
            </section>
            
            <section>
                <h2 class="text-xl font-semibold mb-3">5. Your Rights</h2>
                <p>You have the right to decline biometric enrollment. Alternative attendance methods (such as manual entry by a supervisor or a pin code) are available. You also have the right to revoke your consent at any time via your user dashboard, which will result in the immediate deletion of your biometric data.</p>
            </section>
        </div>
        
        <div class="mt-12 pt-6 border-t border-gray-200">
            <a href="{{ url()->previous() }}" class="inline-flex items-center text-emerald-600 hover:text-emerald-700 font-medium">
                &larr; Back
            </a>
        </div>
    </div>
@endsection
