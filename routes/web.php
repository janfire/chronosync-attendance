<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BiometricController;
use App\Http\Controllers\WebAuthnController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BillingController;
use Illuminate\Support\Facades\Route;

// SuperAdmin Routes (Global Management)
Route::prefix('superadmin')->middleware(['auth', 'platform_admin'])->group(function () {
    Route::get('/dashboard',      [\App\Http\Controllers\Admin\PlatformAdminController::class, 'dashboard'])->name('superadmin.dashboard');

    // Tenant list & DataTables AJAX
    Route::middleware(['platform_role:platform_support'])->group(function () {
        Route::get('/tenants',          [\App\Http\Controllers\Admin\PlatformAdminController::class, 'tenants'])->name('superadmin.tenants');
        Route::get('/tenants/data',     [\App\Http\Controllers\Admin\PlatformAdminController::class, 'tenantsData'])->name('superadmin.tenants.data');
        Route::get('/tenants/summary',  [\App\Http\Controllers\Admin\PlatformAdminController::class, 'tenantsSummary'])->name('superadmin.tenants.summary');
        Route::get('/tenants/{tenant}', [\App\Http\Controllers\Admin\PlatformAdminController::class, 'showTenant'])->name('superadmin.tenants.show');
    });

    // Per-tenant actions (Require full admin)
    Route::middleware(['platform_role:platform_admin'])->group(function () {
        Route::post('/tenants/{tenant}/activate',          [\App\Http\Controllers\Admin\PlatformAdminController::class, 'activateTenant'])->name('superadmin.tenants.activate');
        Route::post('/tenants/{tenant}/suspend',           [\App\Http\Controllers\Admin\PlatformAdminController::class, 'suspendTenant'])->name('superadmin.tenants.suspend');
        Route::post('/tenants/{tenant}/reset-subscription',[\App\Http\Controllers\Admin\PlatformAdminController::class, 'resetTenantSubscription'])->name('superadmin.tenants.reset-subscription');
        Route::delete('/tenants/{tenant}',                 [\App\Http\Controllers\Admin\PlatformAdminController::class, 'destroyTenant'])->name('superadmin.tenants.destroy');
    });

    // Finance
    Route::middleware(['platform_role:platform_finance'])->group(function () {
        Route::get('/finance/pending', [\App\Http\Controllers\Admin\FinanceController::class, 'pendingInvoices'])->name('superadmin.finance.pending');
        Route::post('/finance/invoice/{invoice}/confirm', [\App\Http\Controllers\Admin\FinanceController::class, 'confirmPayment'])->name('superadmin.finance.confirm');
    });

    // System Audit
    Route::middleware(['platform_role:platform_developer'])->group(function () {
        Route::get('/audit', [\App\Http\Controllers\Admin\PlatformAuditController::class, 'index'])->name('superadmin.audit.index');
        Route::get('/audit/{log}', [\App\Http\Controllers\Admin\PlatformAuditController::class, 'show'])->name('superadmin.audit.show');
        Route::post('/audit/{log}/resolve', [\App\Http\Controllers\Admin\PlatformAuditController::class, 'resolve'])->name('superadmin.audit.resolve');
        Route::post('/audit/resolve-all', [\App\Http\Controllers\Admin\PlatformAuditController::class, 'resolveAll'])->name('superadmin.audit.resolve-all');
    });

    // System Users
    Route::middleware(['platform_role:platform_admin'])->group(function () {
        Route::post('users/{user}/resend-invitation', [\App\Http\Controllers\Admin\PlatformSystemUserController::class, 'resendInvitation'])->name('superadmin.users.resend-invitation');
        Route::resource('users', \App\Http\Controllers\Admin\PlatformSystemUserController::class)->names([
            'index' => 'superadmin.users.index',
            'create' => 'superadmin.users.create',
            'store' => 'superadmin.users.store',
            'edit' => 'superadmin.users.edit',
            'update' => 'superadmin.users.update',
            'destroy' => 'superadmin.users.destroy',
        ]);
    });
});

// SaaS Onboarding Routes
Route::get('/get-started', [\App\Http\Controllers\OnboardingController::class, 'showSignup'])->name('onboarding.signup');
Route::post('/get-started', [\App\Http\Controllers\OnboardingController::class, 'register'])->name('onboarding.register');
Route::post('/get-started/check-subdomain', [\App\Http\Controllers\OnboardingController::class, 'checkSubdomain'])->name('onboarding.check_subdomain');

Route::middleware(['tenant'])->group(function () {
    Route::redirect('/', '/attendance/clock');

    // Biometric Policy Route
    Route::get('/biometric-policy', function () {
        return view('pages.biometric-policy');
    })->name('policy.biometric');

    // Session Keep-Alive Route
    Route::get('/session-keep-alive', function () {
        return response()->json(['status' => 'active']);
    })->name('session.keep-alive');

    // Registration Routes
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:60,1');
    
    // OTP Verification Routes
    Route::get('/register/verify-otp', [RegisterController::class, 'showVerifyOtpForm'])->name('register.otp');
    Route::post('/register/verify-otp', [RegisterController::class, 'verifyOtp'])->name('register.verify-otp')->middleware('throttle:10,1');
    Route::post('/register/resend-otp', [RegisterController::class, 'resendOtp'])->name('register.resend-otp')->middleware('throttle:3,1');

    // Attendance Routes (Publicly accessible but tenant-scoped)
    Route::get('/attendance/qr', [AttendanceController::class, 'showQRCode'])->name('attendance.qr')->middleware('feature:QR Codes');
    Route::get('/attendance/qr/generate', [AttendanceController::class, 'generateQRCode'])->name('attendance.qr.generate')->middleware(['throttle:20,1', 'feature:QR Codes']);
    Route::get('/attendance/clock', [AttendanceController::class, 'showClockPage'])->name('attendance.clock');
    Route::post('/attendance/verify', [AttendanceController::class, 'verifyAndClock'])->name('attendance.verify')->middleware('throttle:60,1');
    Route::post('/attendance/exception/request', [\App\Http\Controllers\AttendanceExceptionController::class, 'store'])->name('attendance.exception.request')->middleware('auth');
    Route::post('/attendance/verify-fingerprint', [AttendanceController::class, 'verifyFingerprint'])->name('attendance.verify.fingerprint')->middleware('throttle:60,1');
    Route::get('/attendance/logs', [AttendanceController::class, 'getAttendanceLogs'])->name('attendance.logs');
    Route::get('/attendance/summary/{user_id}', [AttendanceController::class, 'showSummary'])->name('attendance.summary')->middleware('signed');
    Route::post('/attendance/manual-clock-out', [AttendanceController::class, 'manualClockOut'])->name('attendance.manual-clock-out')->middleware('throttle:10,1');

    // Biometric Routes (public registration and tenant-authenticated enrollment)
    Route::get('/biometric/enrollment/{user?}', [BiometricController::class, 'showEnrollment'])->name('biometric.enrollment');
    Route::post('/biometric/facial/enroll', [BiometricController::class, 'storeFacialData'])->name('biometric.facial.enroll')->middleware('throttle:60,1');

    Route::get('/biometric/webauthn/register/options', [WebAuthnController::class, 'registerOptions'])->name('biometric.registration.options');
    Route::post('/biometric/webauthn/register/verify', [WebAuthnController::class, 'registerVerify'])->name('biometric.verify.registration');
    Route::get('/biometric/webauthn/login/options', [WebAuthnController::class, 'loginOptions'])->name('biometric.webauthn.login.options');
    Route::post('/biometric/webauthn/login/verify', [WebAuthnController::class, 'loginVerify'])->name('biometric.webauthn.login.verify');

    Route::post('/biometric/fingerprint/enroll', [BiometricController::class, 'enrollFingerprint'])->name('biometric.fingerprint.enroll')->middleware('throttle:60,1');
    Route::delete('/biometric/fingerprint', [BiometricController::class, 'deleteFingerprint'])->name('biometric.fingerprint.delete');
    Route::delete('/biometric/facial/{user}', [BiometricController::class, 'deleteFacialDataForUser'])->name('biometric.facial.delete.user')->middleware(['admin']);
    Route::post('/biometric/facial/verify', [BiometricController::class, 'verifyFacialData'])->name('biometric.facial.verify')->middleware('throttle:10,1');
    Route::get('/biometric/templates', [BiometricController::class, 'getTemplates'])->name('biometric.templates');
    Route::get('/biometric/complete', [BiometricController::class, 'completeEnrollment'])->name('biometric.complete');
    Route::get('/biometric/status', [BiometricController::class, 'getEnrollmentStatus'])->name('biometric.status');

    // Billing Routes (Accessible even if subscription expired)
    Route::middleware(['auth'])->group(function () {
        Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
        Route::post('/billing/renew', [BillingController::class, 'renew'])->name('billing.renew');
        Route::post('/billing/change-plan', [BillingController::class, 'changePlan'])->name('billing.change-plan');
        Route::get('/billing/invoice/{invoice}', [BillingController::class, 'show'])->name('billing.invoice');
        Route::post('/billing/invoice/{invoice}/proof', [BillingController::class, 'uploadProof'])->name('billing.upload-proof');
    });

    // Protected Routes
    Route::middleware(['auth', 'subscription'])->group(function () {
        // Admin Routes
        Route::prefix('admin')->middleware(['admin'])->group(function () {
            Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
            Route::get('/guide', [AdminController::class, 'userGuide'])->name('admin.guide');
            Route::get('/staff/{user}', [AdminController::class, 'showEmployee'])->name('admin.staff.show');
            Route::get('/insights', [AdminController::class, 'hrInsights'])->name('admin.insights')->middleware('feature:Advanced Analytics');
            
            // User Management Routes
            Route::resource('users', \App\Http\Controllers\UserManagementController::class)->names([
                'index' => 'admin.users.index',
                'create' => 'admin.users.create',
                'store' => 'admin.users.store',
                'edit' => 'admin.users.edit',
                'update' => 'admin.users.update',
                'destroy' => 'admin.users.destroy',
            ]);
            Route::post('users/bulk-destroy', [\App\Http\Controllers\UserManagementController::class, 'bulkDestroy'])
                ->name('admin.users.bulk-destroy');

            // Advanced Reports Routes
            Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('admin.reports.index');
            Route::get('/reports/settings', [\App\Http\Controllers\ReportController::class, 'settings'])->name('admin.reports.settings');
            Route::post('/reports/settings', [\App\Http\Controllers\ReportController::class, 'updateSettings'])->name('admin.reports.settings.update');
            Route::get('/reports/fetch-holidays', [\App\Http\Controllers\ReportController::class, 'fetchHolidays'])->name('admin.reports.fetch.holidays');
            Route::get('/reports/export', [\App\Http\Controllers\ReportController::class, 'exportMonthly'])->name('admin.reports.export');
            Route::get('/reports/export-json', [\App\Http\Controllers\ReportController::class, 'exportMonthlyJson'])->name('admin.reports.export.json');

            // Attendance Exceptions / Corrections
            Route::get('/exceptions', [\App\Http\Controllers\AttendanceExceptionController::class, 'index'])->name('admin.exceptions.index');
            Route::post('/exceptions/{exception}/approve', [\App\Http\Controllers\AttendanceExceptionController::class, 'approve'])->name('admin.exceptions.approve');
            Route::post('/exceptions/{exception}/reject', [\App\Http\Controllers\AttendanceExceptionController::class, 'reject'])->name('admin.exceptions.reject');
        });

        // Profile Routes
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/biometric/revoke', [\App\Http\Controllers\PrivacySettingsController::class, 'revokeConsent'])->name('profile.biometric.revoke');

        // Staff Dashboard
        Route::get('/staff/dashboard', [\App\Http\Controllers\StaffDashboardController::class, 'index'])->name('staff.dashboard');
    });

    // Auth Routes (Login/Logout/etc. don't need subscription check)
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/login/pending', [LoginController::class, 'showPending'])->name('login.pending');
    Route::get('/login/check-approval', [LoginController::class, 'checkApproval'])->name('login.check-approval');
    Route::get('/login/approve/{token}', [LoginController::class, 'approveLogin'])->name('login.approve');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::post('/biometric/update-login', [LoginController::class, 'loginForBiometricUpdate'])
        ->name('biometric.update-login')
        ->middleware('throttle:10,1');

    // Google OAuth Routes
    Route::get('/auth/google/redirect', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback'])->name('auth.google.callback');
    Route::get('/auth/staff/complete', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'showStaffPrompt'])->name('auth.staff.prompt');
    Route::post('/auth/staff/complete', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'completeStaffPrompt'])->name('auth.staff.complete');

    Route::get('/dashboard', function () {
        if (Auth::user()->isPlatformAdmin()) {
            return redirect()->route('superadmin.dashboard');
        } elseif (Auth::user()->isStaff() || Auth::user()->isGeneralUser()) {
            return redirect()->route('staff.dashboard');
        }
        return redirect()->route('admin.dashboard');
    })->name('dashboard')->middleware('auth');
});

// ==========================================
// SYSTEM AUDIT TESTING ROUTES
// ==========================================
Route::get('/test-crash', function() {
    throw new \Exception("This is a deliberate test crash to trigger the System Audit feature!");
});

Route::get('/preview-500', function() {
    return view('errors.500', ['reference_code' => 'ERR-TEST99']);
});

Route::get('/debug-env', function() {
    return [
        'app_url' => config('app.url'),
        'baseHost' => parse_url(config('app.url'), PHP_URL_HOST),
        'host' => request()->getHost(),
    ];
});
