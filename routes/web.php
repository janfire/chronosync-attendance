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
    Route::get('/finance/pending', [\App\Http\Controllers\Admin\FinanceController::class, 'pendingInvoices'])->name('superadmin.finance.pending');
    Route::post('/finance/invoice/{invoice}/confirm', [\App\Http\Controllers\Admin\FinanceController::class, 'confirmPayment'])->name('superadmin.finance.confirm');
});

// SaaS Onboarding Routes
Route::get('/get-started', [\App\Http\Controllers\OnboardingController::class, 'showSignup'])->name('onboarding.signup');
Route::post('/get-started', [\App\Http\Controllers\OnboardingController::class, 'register'])->name('onboarding.register');

Route::middleware(['tenant'])->group(function () {
    Route::redirect('/', '/attendance/clock');

    // Registration Routes
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:60,1');

    // Attendance Routes (Publicly accessible but tenant-scoped)
    Route::get('/attendance/qr', [AttendanceController::class, 'showQRCode'])->name('attendance.qr');
    Route::get('/attendance/qr/generate', [AttendanceController::class, 'generateQRCode'])->name('attendance.qr.generate')->middleware('throttle:20,1');
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
            Route::get('/insights', [AdminController::class, 'hrInsights'])->name('admin.insights');
            
            // User Management Routes
            Route::resource('users', \App\Http\Controllers\UserManagementController::class)->names([
                'index' => 'admin.users.index',
                'create' => 'admin.users.create',
                'store' => 'admin.users.store',
                'edit' => 'admin.users.edit',
                'update' => 'admin.users.update',
                'destroy' => 'admin.users.destroy',
            ]);

            // Advanced Reports Routes
            Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('admin.reports.index');
            Route::get('/reports/settings', [\App\Http\Controllers\ReportController::class, 'settings'])->name('admin.reports.settings');
            Route::post('/reports/settings', [\App\Http\Controllers\ReportController::class, 'updateSettings'])->name('admin.reports.settings.update');
            Route::get('/reports/fetch-holidays', [\App\Http\Controllers\ReportController::class, 'fetchHolidays'])->name('admin.reports.fetch.holidays');
            Route::get('/reports/export', [\App\Http\Controllers\ReportController::class, 'exportMonthly'])->name('admin.reports.export');

            // Scores & Grades Routes
            Route::get('/scores', [\App\Http\Controllers\Admin\AttendanceScoreController::class, 'index'])->name('admin.scores.index');
            Route::get('/scores/export', [\App\Http\Controllers\Admin\AttendanceScoreController::class, 'export'])->name('admin.scores.export');
            Route::get('/scores/{user}', [\App\Http\Controllers\Admin\AttendanceScoreController::class, 'show'])->name('admin.scores.show');

            // Attendance Exceptions / Corrections
            Route::get('/exceptions', [\App\Http\Controllers\AttendanceExceptionController::class, 'index'])->name('admin.exceptions.index');
            Route::post('/exceptions/{exception}/approve', [\App\Http\Controllers\AttendanceExceptionController::class, 'approve'])->name('admin.exceptions.approve');
            Route::post('/exceptions/{exception}/reject', [\App\Http\Controllers\AttendanceExceptionController::class, 'reject'])->name('admin.exceptions.reject');
        });

        // Profile Routes
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });

    // Auth Routes (Login/Logout/etc. don't need subscription check)
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::post('/biometric/update-login', [LoginController::class, 'loginForBiometricUpdate'])
        ->name('biometric.update-login')
        ->middleware('throttle:10,1');

    Route::get('/dashboard', function () {
        return redirect()->route('admin.dashboard');
    })->name('dashboard')->middleware('auth');
});
