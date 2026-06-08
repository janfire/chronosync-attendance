@extends('admin.layout')

@section('title', 'Admin Setup Guide')
@section('page-title', 'Attendance Admin Guide')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    <!-- Intro Banner -->
    <div class="bg-emerald-600 rounded-2xl p-8 text-white shadow-md relative overflow-hidden">
        <div class="absolute right-0 bottom-0 opacity-10 transform translate-x-12 translate-y-12">
            <i class="fas fa-book-open text-9xl"></i>
        </div>
        <div class="relative z-10 max-w-2xl">
            <h3 class="text-3xl font-bold mb-2">Administrator Guide</h3>
            <p class="text-emerald-50 text-sm leading-relaxed">
                Use this page to complete the most common administrative tasks in the attendance system: add staff, enroll biometric credentials, set up the clocking station, and review reports.
            </p>
        </div>
    </div>

    <!-- Quick Navigation Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="#staff-management" class="p-6 bg-white rounded-xl border border-gray-100 hover:border-emerald-500 shadow-sm hover:shadow-md transition-all flex items-start space-x-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                <i class="fas fa-users-cog text-xl"></i>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900 mb-1">Staff Management</h4>
                <p class="text-xs text-gray-500">Add employees and complete biometric enrollment.</p>
            </div>
        </a>
        <a href="#attendance-clocking" class="p-6 bg-white rounded-xl border border-gray-100 hover:border-emerald-500 shadow-sm hover:shadow-md transition-all flex items-start space-x-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                <i class="fas fa-qrcode text-xl"></i>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900 mb-1">Clocking Station</h4>
                <p class="text-xs text-gray-500">Set up the QR station and confirm clock-in methods.</p>
            </div>
        </a>
        <a href="#reports-analytics" class="p-6 bg-white rounded-xl border border-gray-100 hover:border-emerald-500 shadow-sm hover:shadow-md transition-all flex items-start space-x-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                <i class="fas fa-file-contract text-xl"></i>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900 mb-1">Reports & Billing</h4>
                <p class="text-xs text-gray-500">Review attendance summaries, exceptions, and subscription status.</p>
            </div>
        </a>
    </div>

    <!-- Main Content Sections -->
    <div class="space-y-12">
        
        <!-- Section: Staff & Enrollment -->
        <section id="staff-management" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="p-2 bg-emerald-500 text-white rounded-lg"><i class="fas fa-users-cog"></i></span>
                    <h3 class="text-lg font-bold text-gray-900">1. Staff Management & Biometric Enrollment</h3>
                </div>
                <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full">Core Setup</span>
            </div>
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-900">Adding Employees</h4>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            From the sidebar, choose <strong>Staff Management</strong> and click <strong>Add New Employee</strong>. Enter the employee's full name, email address, and employee number. Save the record before enrolling biometric credentials.
                        </p>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-800 flex items-start space-x-3">
                            <i class="fas fa-info-circle mt-0.5 text-base shrink-0"></i>
                            <div>
                                <strong>Note:</strong> Each email address must be unique. Employee IDs should also be unique within the system.
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-900">Biometric Enrollment</h4>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            To enroll biometric authentication, open the employee's profile and choose the available enrollment option. Common steps include:
                        </p>
                        <ul class="space-y-2 text-xs text-gray-600">
                            <li class="flex items-center space-x-2">
                                <i class="fas fa-check-circle text-emerald-500"></i>
                                <span>Open the employee record in <strong>Staff Management</strong>.</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fas fa-check-circle text-emerald-500"></i>
                                <span>Select <strong>Facial Recognition Enrollment</strong> or another supported method.</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fas fa-check-circle text-emerald-500"></i>
                                <span>Allow camera access and follow the prompts to capture the employee's face.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section: Clocking & QR Code -->
        <section id="attendance-clocking" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="p-2 bg-emerald-500 text-white rounded-lg"><i class="fas fa-qrcode"></i></span>
                    <h3 class="text-lg font-bold text-gray-900">2. Attendance Verification & QR Code</h3>
                </div>
                <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full">Daily Operations</span>
            </div>
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-900">Set Up the QR Station</h4>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Open <strong>QR Code</strong> from the sidebar and place the screen where employees arrive. The QR code refreshes automatically every 15 seconds to keep the clock-in process secure.
                        </p>
                    </div>
                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-900">How Employees Clock In</h4>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Employees can record attendance using one of these methods:
                        </p>
                        <ol class="space-y-2 text-xs text-gray-600 list-decimal pl-4">
                            <li>
                                <strong>QR Scan:</strong> Scan the station QR code with a phone camera.
                            </li>
                            <li>
                                <strong>Facial Verification:</strong> Use the enrolled facial biometric method at the terminal.
                            </li>
                            <li>
                                <strong>Security Keys:</strong> Authenticate with WebAuthn-compatible credentials such as Windows Hello or Touch ID.
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section: Reports, Rules & Billing -->
        <section id="reports-analytics" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="p-2 bg-emerald-500 text-white rounded-lg"><i class="fas fa-file-contract"></i></span>
                    <h3 class="text-lg font-bold text-gray-900">3. Reports, Rules & Billing</h3>
                </div>
                <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full">Management</span>
            </div>
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="space-y-3">
                        <h4 class="font-semibold text-gray-950">Shift & Attendance Rules</h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Use <strong>Advanced Reports</strong> to define standard shift hours, grace periods, and local holidays. These settings determine how attendance records are evaluated.
                        </p>
                    </div>
                    <div class="space-y-3">
                        <h4 class="font-semibold text-gray-950">Attendance Metrics</h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Review the scores section for lateness, overtime, early departure, and consistency. These metrics help identify where adjustments are needed.
                        </p>
                    </div>
                    <div class="space-y-3">
                        <h4 class="font-semibold text-gray-950">Subscription & Billing</h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Visit <strong>My Subscription</strong> to view invoices, renew your plan, or upload proof of payment. Keep billing details current to avoid service interruptions.
                        </p>
                    </div>
                </div>
            </div>
        </section>

    </div>

    <!-- Help & Support -->
    <div class="p-8 bg-gray-100 rounded-2xl border border-gray-200 flex flex-col md:flex-row items-center justify-between">
        <div class="space-y-2 mb-4 md:mb-0">
            <h4 class="font-bold text-gray-900">Need assistance?</h4>
            <p class="text-sm text-gray-600">If you need help with enrollment, attendance issues, or billing, contact your internal support team or system administrator.</p>
        </div>
        <a href="mailto:support@zou-attendance.test" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold shadow-sm hover:shadow-md transition-all">
            <i class="fas fa-envelope mr-2"></i> Email Support
        </a>
    </div>
</div>
@endsection
