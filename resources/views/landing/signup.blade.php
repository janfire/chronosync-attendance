<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Get Started - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f9fafb; /* Light gray background */
            position: relative;
        }
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%230f2a1d' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            position: absolute;
            inset: 0;
            z-index: -1;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 bg-gray-50 relative">
    <div class="bg-pattern"></div>
    <div class="max-w-lg w-full rounded-3xl overflow-hidden shadow-[0_32px_64px_-16px_rgba(0,0,0,0.1)] bg-white min-h-[560px] relative z-10">
        <div class="w-full p-6 lg:p-8">
            <div class="mb-8 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-500 text-white mb-4">
                    <i class="fas fa-clock"></i>
                </div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-emerald-600 mb-3">ChronoSync</p>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Create your workspace</h1>
            </div>

            <div id="step-1" class="space-y-5">
                <div class="space-y-3 rounded-3xl border border-gray-100 bg-gray-50 p-5 text-left">
                    <h2 class="text-lg font-semibold text-gray-900">Welcome to Workspace Creation</h2>
                    <p class="text-gray-600 text-sm leading-6">This process creates a new ChronoSync workspace for your company. You'll enter company details, and we'll generate your workspace subdomain automatically.</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li>• We'll create a dedicated workspace for your company.</li>
                        <li>• Your workspace URL is generated for you.</li>
                        <li>• After completion, you'll be taken to your dashboard.</li>
                    </ul>
                </div>
                <button id="nextBtn" class="w-full py-3 rounded-2xl bg-[#0f2a1d] text-white font-semibold shadow-sm hover:bg-emerald-900 transition">Next</button>
            </div>

            <form id="onboardingForm" action="{{ route('onboarding.register') }}" method="POST" class="space-y-5">
                @csrf
                <div id="step-2" class="hidden space-y-6">
                    <div class="space-y-3">
                        <h2 class="text-lg font-semibold text-gray-900">Company details</h2>
                        <p class="text-gray-500 text-sm">Enter your company name and email. This email will be used to log in.</p>
                    </div>

                    <div id="errorContainer" class="hidden rounded-2xl border border-rose-100 bg-rose-50 p-4 text-sm text-rose-700"></div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Company Name</label>
                            <input id="companyName" type="text" name="company_name" value="{{ old('company_name') }}" required placeholder="e.g. Acme Zimbabwe" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Company Email</label>
                            <input id="companyEmail" type="email" name="email" value="{{ old('email') }}" required placeholder="contact@acme.co.zw" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10">
                        </div>
                    </div>

                    <input type="hidden" name="subdomain" id="subdomain" value="">

                    <div class="flex justify-end">
                        <button id="companyNextBtn" type="button" class="rounded-2xl bg-[#0f2a1d] py-3 px-6 text-sm font-semibold text-white shadow-sm hover:bg-emerald-900 transition">Next</button>
                    </div>
                </div>

                <div id="step-3" class="hidden space-y-6">
                <div class="space-y-3">
                    <h2 class="text-lg font-semibold text-gray-900">Account owner</h2>
                    <p class="text-gray-500 text-sm">Enter the administrator name and password for your workspace.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Full Name</label>
                        <input type="text" name="admin_name" value="{{ old('admin_name') }}" required placeholder="Mubatsiri Masiya" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" required class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10">
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <button id="backToCompanyBtn" type="button" class="rounded-2xl border border-gray-200 bg-white py-3 px-6 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">Back</button>
                    <button type="submit" class="rounded-2xl bg-[#0f2a1d] py-3 px-6 text-sm font-semibold text-white shadow-sm hover:bg-emerald-900 transition">Create Workspace</button>
                </div>
            </div>
        </form>

            <div id="step-4" class="hidden space-y-6 text-center">
                <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-6">
                    <p class="text-sm font-semibold text-emerald-700">Workspace created successfully!</p>
                    <p class="mt-3 text-gray-700">Your workspace is ready at:</p>
                    <p id="workspaceUrl" class="mt-2 font-medium text-gray-900 break-words"></p>
                    <p class="mt-3 text-gray-500 text-sm">Use this URL to share with your users. Redirecting to your dashboard now.</p>
                    <p class="mt-4 text-xs text-gray-400" id="redirectCountdown">Redirecting in 5 seconds...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const step1 = document.getElementById('step-1');
        const step2 = document.getElementById('step-2');
        const step3 = document.getElementById('step-3');
        const step4 = document.getElementById('step-4');
        const nextBtn = document.getElementById('nextBtn');
        const companyNextBtn = document.getElementById('companyNextBtn');
        const backToCompanyBtn = document.getElementById('backToCompanyBtn');
        const onboardingForm = document.getElementById('onboardingForm');
        const companyName = document.getElementById('companyName');
        const companyEmail = document.getElementById('companyEmail');
        const subdomainInput = document.getElementById('subdomain');
        const errorContainer = document.getElementById('errorContainer');
        const workspaceUrl = document.getElementById('workspaceUrl');
        const redirectCountdown = document.getElementById('redirectCountdown');

        function slugify(value) {
            return value.toString().toLowerCase().trim()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .substring(0, 50) || 'workspace';
        }

        function updateSubdomain() {
            const slug = slugify(companyName.value || 'workspace');
            subdomainInput.value = slug;
        }

        function showError(message) {
            errorContainer.innerHTML = `<p>${message}</p>`;
            errorContainer.classList.remove('hidden');
        }

        nextBtn.addEventListener('click', () => {
            step1.classList.add('hidden');
            step2.classList.remove('hidden');
            companyName.focus();
        });

        companyNextBtn.addEventListener('click', () => {
            if (!companyName.value.trim() || !companyEmail.value.trim()) {
                showError('Company name and email are required.');
                return;
            }

            errorContainer.classList.add('hidden');
            updateSubdomain();
            step2.classList.add('hidden');
            step3.classList.remove('hidden');
        });

        backToCompanyBtn.addEventListener('click', () => {
            step3.classList.add('hidden');
            step2.classList.remove('hidden');
        });

        companyName.addEventListener('input', updateSubdomain);

        onboardingForm.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !step2.classList.contains('hidden')) {
                event.preventDefault();
                companyNextBtn.click();
            }
        });

        onboardingForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            errorContainer.classList.add('hidden');
            errorContainer.innerHTML = '';

            const formData = new FormData(onboardingForm);
            const payload = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(onboardingForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify(payload),
                });

                if (response.ok) {
                    const data = await response.json();
                    step3.classList.add('hidden');
                    step4.classList.remove('hidden');
                    workspaceUrl.textContent = data.workspace_url;

                    let seconds = 5;
                    redirectCountdown.textContent = `Redirecting in ${seconds} seconds...`;
                    const interval = setInterval(() => {
                        seconds -= 1;
                        redirectCountdown.textContent = `Redirecting in ${seconds} seconds...`;
                        if (seconds <= 0) {
                            clearInterval(interval);
                            window.location.href = data.redirect_url;
                        }
                    }, 1000);
                } else if (response.status === 422) {
                    const data = await response.json();
                    const errors = data.errors || {};
                    const list = document.createElement('ul');
                    list.className = 'list-disc list-inside space-y-1';
                    Object.values(errors).flat().forEach(message => {
                        const item = document.createElement('li');
                        item.textContent = message;
                        list.appendChild(item);
                    });
                    errorContainer.innerHTML = '';
                    errorContainer.appendChild(list);
                    errorContainer.classList.remove('hidden');
                } else {
                    throw new Error('Unable to create workspace. Please try again.');
                }
            } catch (error) {
                errorContainer.textContent = error.message;
                errorContainer.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
