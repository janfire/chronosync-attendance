@extends('admin.layout')

@section('title', 'Create User')
@section('page-title', 'Create New User')

@push('styles')
<style>
    .form-field {
        background-color: #f9fafb;
        border: 1px solid #d1d5db;
        box-shadow: inset 0 1px 1px rgba(15, 23, 42, 0.04);
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
    }

    .form-field:hover {
        border-color: #9ca3af;
        background-color: #ffffff;
    }

    .form-field:focus {
        outline: none;
        border-color: #10b981;
        background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15), inset 0 1px 1px rgba(15, 23, 42, 0.02);
    }
    
    html.dark .form-field {
        background-color: #374151;
        border-color: #4b5563;
        color: #f9fafb;
    }

    html.dark .form-field:hover {
        border-color: #6b7280;
        background-color: #4b5563;
    }

    html.dark .form-field:focus {
        border-color: #10b981;
        background-color: #374151;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15), inset 0 1px 1px rgba(15, 23, 42, 0.02);
    }
</style>
@endpush

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-200 dark:border-gray-700 px-8 py-7 sm:px-10">
            <div class="mb-6 border-b border-gray-100 dark:border-gray-700 pb-5">
                <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-500">Admin / Users / Create</p>
                <h3 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">Create a staff account</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Fill out all required fields below to create a new user profile.</p>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-7" id="create-user-form">
                @csrf

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 md:gap-7">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Full Name <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="{{ old('name') }}"
                            required
                            class="form-field w-full px-4 py-3 rounded-xl text-sm text-gray-800 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none @error('name') border-red-500 @enderror"
                            placeholder="Enter full name"
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Email Address <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}"
                            required
                            class="form-field w-full px-4 py-3 rounded-xl text-sm text-gray-800 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none @error('email') border-red-500 @enderror"
                            placeholder="staff.member@example.com"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Must be a valid company email address.</p>
                    </div>
                </div>

                <div>
                    <label for="employee_number" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Employee Number <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="employee_number" 
                        name="employee_number" 
                        value="{{ old('employee_number') }}"
                        required
                        class="form-field w-full px-4 py-3 rounded-xl text-sm text-gray-800 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none @error('employee_number') border-red-500 @enderror"
                        placeholder="ChronoSync-001"
                    >
                    @error('employee_number')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="role" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="role" 
                        name="role" 
                        required
                        class="form-field w-full px-4 py-3 rounded-xl text-sm text-gray-800 dark:text-white focus:outline-none @error('role') border-red-500 @enderror"
                    >
                        <option value="">Select Role</option>
                        @foreach($roles as $key => $label)
                            <option value="{{ $key }}" {{ old('role') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <div class="mt-3 rounded-xl border border-emerald-100 dark:border-emerald-900/50 bg-emerald-50/60 dark:bg-emerald-900/20 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-800 dark:text-emerald-400">Role Guidance</p>
                        <p id="role-help" class="mt-1 text-sm text-emerald-900 dark:text-emerald-300">Choose a role to preview the permissions this user will have.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 md:gap-7">
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Password <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            minlength="8"
                            class="form-field w-full px-4 py-3 rounded-xl text-sm text-gray-800 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none @error('password') border-red-500 @enderror"
                            placeholder="Minimum 8 characters"
                        >
                        @error('password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p id="password-help" class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use at least 8 characters with a mix of letters, numbers, and symbols.</p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            required
                            minlength="8"
                            class="form-field w-full px-4 py-3 rounded-xl text-sm text-gray-800 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none"
                            placeholder="Re-enter password"
                        >
                        <p id="password-match-help" class="mt-1 text-xs text-gray-500 dark:text-gray-400">Re-enter the same password to confirm.</p>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 pt-5 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" id="create-user-submit" class="inline-flex items-center px-6 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700 active:bg-emerald-800 disabled:bg-emerald-300 disabled:cursor-not-allowed transition-colors">
                        <i class="fas fa-user-plus mr-2"></i>Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const roleSelect = document.getElementById('role');
        const roleHelp = document.getElementById('role-help');
        const password = document.getElementById('password');
        const passwordConfirmation = document.getElementById('password_confirmation');
        const passwordHelp = document.getElementById('password-help');
        const passwordMatchHelp = document.getElementById('password-match-help');
        const form = document.getElementById('create-user-form');
        const submitButton = document.getElementById('create-user-submit');

        const roleDescriptions = {
            super_admin: 'Super Admin has full system access, including user and workspace management.',
            admin: 'Admin can manage employees, attendance, reports, and operational settings.',
            general_user: 'General User has limited access to reports and attendance information.',
            staff: 'Staff can perform basic attendance actions like clock-in and clock-out.'
        };

        const updateRoleHelp = () => {
            const selectedRole = roleSelect?.value || '';
            if (!selectedRole) {
                roleHelp.textContent = 'Choose a role to preview the permissions this user will have.';
                return;
            }

            roleHelp.textContent = roleDescriptions[selectedRole] || 'Role permissions will be applied based on your system configuration.';
        };

        const updatePasswordStrength = () => {
            const value = password?.value || '';
            if (!value) {
                passwordHelp.textContent = 'Use at least 8 characters with a mix of letters, numbers, and symbols.';
                passwordHelp.className = 'mt-1 text-xs text-gray-500';
                return;
            }

            let score = 0;
            if (value.length >= 8) score++;
            if (/[A-Z]/.test(value) && /[a-z]/.test(value)) score++;
            if (/\d/.test(value)) score++;
            if (/[^A-Za-z0-9]/.test(value)) score++;

            if (score <= 1) {
                passwordHelp.textContent = 'Weak password: add length, numbers, and symbols.';
                passwordHelp.className = 'mt-1 text-xs text-red-600';
            } else if (score <= 3) {
                passwordHelp.textContent = 'Good password: add one more complexity element for better security.';
                passwordHelp.className = 'mt-1 text-xs text-amber-600';
            } else {
                passwordHelp.textContent = 'Strong password.';
                passwordHelp.className = 'mt-1 text-xs text-emerald-600';
            }
        };

        const updatePasswordMatch = () => {
            const pwd = password?.value || '';
            const confirm = passwordConfirmation?.value || '';

            if (!confirm) {
                passwordMatchHelp.textContent = 'Re-enter the same password to confirm.';
                passwordMatchHelp.className = 'mt-1 text-xs text-gray-500';
                return;
            }

            if (pwd === confirm) {
                passwordMatchHelp.textContent = 'Passwords match.';
                passwordMatchHelp.className = 'mt-1 text-xs text-emerald-600';
            } else {
                passwordMatchHelp.textContent = 'Passwords do not match yet.';
                passwordMatchHelp.className = 'mt-1 text-xs text-red-600';
            }
        };

        roleSelect?.addEventListener('change', updateRoleHelp);
        password?.addEventListener('input', () => {
            updatePasswordStrength();
            updatePasswordMatch();
        });
        passwordConfirmation?.addEventListener('input', updatePasswordMatch);

        form?.addEventListener('submit', () => {
            if (!form.checkValidity()) return;
            if (!submitButton) return;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
        });

        updateRoleHelp();
        updatePasswordStrength();
        updatePasswordMatch();

        const showNotice = (title, text, icon = 'info') => {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({
                    title,
                    text,
                    icon,
                    confirmButtonColor: '#059669'
                });
                return;
            }

            alert(text);
        };

        @if(session('success'))
            showNotice('Success', @json(session('success')), 'success');
        @endif

        @if(session('error'))
            showNotice('Error', @json(session('error')), 'error');
        @endif

        @if($errors->any())
            showNotice('Validation Error', @json($errors->first()), 'warning');
        @endif
    });
</script>
@endpush