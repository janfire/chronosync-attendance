@extends('admin.layout')

@section('title', 'User Navigation Guide')
@section('page-title', 'System Navigation Guide')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    <!-- Intro Banner -->
    <div class="bg-emerald-600 rounded-2xl p-8 text-white shadow-md relative overflow-hidden">
        <div class="absolute right-0 bottom-0 opacity-10 transform translate-x-12 translate-y-12">
            <i class="fas fa-book-open text-9xl"></i>
        </div>
        <div class="relative z-10 max-w-2xl">
            <h3 class="text-3xl font-bold mb-2">Welcome to ChronoSync</h3>
            <p class="text-emerald-50 text-sm leading-relaxed">
                This comprehensive guide is designed to help you, as the primary system administrator, configure and seamlessly navigate through the ChronoSync Attendance system. Below you'll find step-by-step documentation for every module.
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
                <h4 class="font-semibold text-gray-900 mb-1">Staff & Enrollment</h4>
                <p class="text-xs text-gray-500">Manage employees, facial enrollment, and security keys.</p>
            </div>
        </a>
        <a href="#attendance-clocking" class="p-6 bg-white rounded-xl border border-gray-100 hover:border-emerald-500 shadow-sm hover:shadow-md transition-all flex items-start space-x-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                <i class="fas fa-qrcode text-xl"></i>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900 mb-1">Attendance & QR</h4>
                <p class="text-xs text-gray-500">Clocking methods, QR generation, and real-time logs.</p>
            </div>
        </a>
        <a href="#reports-analytics" class="p-6 bg-white rounded-xl border border-gray-100 hover:border-emerald-500 shadow-sm hover:shadow-md transition-all flex items-start space-x-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                <i class="fas fa-file-contract text-xl"></i>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900 mb-1">Reports & Analytics</h4>
                <p class="text-xs text-gray-500">Shift configuration, monthly exports, and performance grades.</p>
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
                            To add your staff to ChronoSync, click on <strong>Staff Management</strong> in the sidebar, and select <strong>Add New Employee</strong>. Fill in their legal name, work email address, and unique Employee ID.
                        </p>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-800 flex items-start space-x-3">
                            <i class="fas fa-info-circle mt-0.5 text-base shrink-0"></i>
                            <div>
                                <strong>Important Info:</strong> Emails must be unique. The system uses unique workspace-scoped subdomains, but corporate domains are allowed across all users!
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-900">Facial & Biometric Enrollment</h4>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            ChronoSync features state-of-the-art secure facial recognition. To enroll an employee:
                        </p>
                        <ul class="space-y-2 text-xs text-gray-600">
                            <li class="flex items-center space-x-2">
                                <i class="fas fa-check-circle text-emerald-500"></i>
                                <span>Navigate to the employee's detail view from <strong>Staff Management</strong>.</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fas fa-check-circle text-emerald-500"></i>
                                <span>Click <strong>Facial Recognition Enrollment</strong>.</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <i class="fas fa-check-circle text-emerald-500"></i>
                                <span>Grant webcam permissions and capture three distinct angles of the face for premium match accuracy.</span>
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
                        <h4 class="font-semibold text-gray-900">The QR Code Station</h4>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Click on <strong>QR Code</strong> in the sidebar to open the dedicated Clocking Station. You can project this screen on a tablet or monitor at your office entrance. The QR code automatically refreshes securely every 15 seconds to prevent replication and spoofing.
                        </p>
                    </div>
                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-900">How Employees Clock In</h4>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Employees have multiple ways to verify attendance:
                        </p>
                        <ol class="space-y-2 text-xs text-gray-600 list-decimal pl-4">
                            <li>
                                <strong>Mobile Scan:</strong> Scan the office QR station with their personal mobile phones.
                            </li>
                            <li>
                                <strong>Facial Recognition:</strong> Look at the terminal camera for instant contactless verification.
                            </li>
                            <li>
                                <strong>Security Keys:</strong> Authenticate securely using WebAuthn-compliant biometric credentials (like Windows Hello or Touch ID).
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
                    <h3 class="text-lg font-bold text-gray-900">3. Advanced Reports, Shifts, & Billings</h3>
                </div>
                <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full">Management</span>
            </div>
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="space-y-3">
                        <h4 class="font-semibold text-gray-950">Shift & Attendance Rules</h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Under <strong>Advanced Reports</strong>, you can access system rules where you set standard shift timings (e.g., 08:00 AM to 04:30 PM), define the grace period (e.g., late after 09:00 AM), and configure local public holidays. These rules automatically evaluate employee performance metrics.
                        </p>
                    </div>
                    <div class="space-y-3">
                        <h4 class="font-semibold text-gray-950">Scores & Performance Metrics</h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Under the <strong>Scores & Grades</strong> section, the system automatically grades employees based on lateness, overtime, early departures, and attendance consistency. You can view overall team rankings and pinpoint scheduling inefficiencies.
                        </p>
                    </div>
                    <div class="space-y-3">
                        <h4 class="font-semibold text-gray-950">Subscriptions & Billings</h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            To ensure uninterrupted access, navigate to <strong>My Subscription</strong>. Here you can upgrade or renew your plan, view system-generated invoices, and securely upload proof of bank transfer payments directly for immediate confirmation by the finance reconciliation pipeline.
                        </p>
                    </div>
                </div>
            </div>
        </section>

    </div>

    <!-- Help & Support -->
    <div class="p-8 bg-gray-100 rounded-2xl border border-gray-200 flex flex-col md:flex-row items-center justify-between">
        <div class="space-y-2 mb-4 md:mb-0">
            <h4 class="font-bold text-gray-900">Still need help or encountering a problem?</h4>
            <p class="text-sm text-gray-600">Our customer onboarding and support team is ready to assist you round the clock.</p>
        </div>
        <a href="mailto:support@chronosync.com" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold shadow-sm hover:shadow-md transition-all">
            <i class="fas fa-envelope mr-2"></i> Contact ChronoSync Support
        </a>
    </div>
</div>
@endsection
