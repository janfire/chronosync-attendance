# ChronoSync Attendance - System Architecture & Developer Onboarding

Welcome to the **ChronoSync Attendance** project! This document is designed to help new developers quickly understand the system architecture, core modules, and integration points, allowing you to kickstart your work efficiently.

## 1. High-Level Architecture

ChronoSync Attendance is a **multi-tenant SaaS application** built on the **Laravel 12** framework. It provides businesses with a comprehensive time and attendance tracking solution, augmented with advanced biometric and hardware integrations.

### Tech Stack
*   **Backend Framework:** Laravel 12 (PHP ^8.2)
*   **Frontend:** Blade Templates, JavaScript (bundled via Vite)
*   **Database:** Relational DB (MySQL/PostgreSQL/SQLite) managed via Eloquent ORM. 
*   **Authentication:** Laravel standard auth, WebAuthn (FIDO2), Microsoft Azure AD (SSO).
*   **Microservices/Daemons:** A separate Python-based Facial Recognition server runs alongside the main PHP application.

---

## 2. Core Modules and Components

The system is modularized primarily through Laravel Services (`app/Services`) and Controllers (`app/Http/Controllers`).

### 2.1 Multi-Tenancy Module
The system isolates data between different client companies (Tenants).
*   **`TenantProvisioningService`**: Handles the onboarding of new organizations, creating the `Tenant` record, provisioning the initial Super Admin user, and seeding default system settings (like shift rules).
*   **Platform Admin**: A "Superadmin" layer exists (under the `/superadmin` routes) to manage all tenants, activate/suspend them, and handle billing and platform audits.
*   *Implementation Note*: Most tenant-specific models (like Users, AttendanceLogs) include a `tenant_id` foreign key. Subdomain routing is also utilized.

### 2.2 Attendance & Scoring Module
The core engine for tracking when employees arrive and leave.
*   **Methods**: QR Code scanning, Manual Clock-In/Out, Fingerprint, and Facial Recognition.
*   **`AttendanceScoringService`**: Evaluates attendance records against shift rules (start time, end time, late thresholds, early out thresholds) to generate attendance scores or flag anomalies.
*   **`AttendanceExceptionController`**: Allows employees to request manual corrections or exceptions for missed punches, which admins can then approve or reject.

### 2.3 Biometric Integrations
ChronoSync Attendance supports multiple forms of biometric authentication to prevent buddy-punching.
*   **Facial Recognition (`FacialRecognitionService`)**: 
    *   Takes base64 images and extracts a 128-d facial encoding.
    *   Communicates with an external Python server (usually on `http://localhost:5001`) for fast processing (using HOG models for scanning, CNN models for enrollment).
    *   Has a fallback CLI mechanism if the Python server is down.
*   **WebAuthn (`WebAuthnController`)**: Implements passwordless authentication and biometric verification (like Apple TouchID/FaceID or Windows Hello) directly through the browser.
*   **ZKTeco Hardware Integration (`ZKTecoService`)**: Interfaces directly with physical ZKTeco fingerprint/attendance devices over the network via IP/Port to enroll users and synchronize attendance logs.

### 2.4 Billing & Subscription Module
*   **`BillingService` & `BillingController`**: Manages tenant subscription plans (e.g., starter), trials, and renewals.
*   Invoices can be generated, and there is a workflow for users to upload "Proof of Payment" which superadmins can verify and confirm.

### 2.5 Reporting & Analytics
*   **`ReportService` & `AnalyticsService`**: Aggregates attendance data, fetches holidays, and generates exports (e.g., monthly summaries).
*   Provides HR insights displayed on the Admin Dashboard.

### 2.6 System Audit Module
*   Tracks platform-wide errors and anomalies (e.g., `SystemErrorLog`). Superadmins can review and resolve system crashes or exceptions via the `PlatformAuditController`.

---

## 3. Request Lifecycle & Routing

The application routes (`routes/web.php`) are divided into distinct scopes:
1.  **`/superadmin/*`**: Global platform management. Middleware ensures only platform admins can access.
2.  **`/get-started`**: SaaS onboarding and tenant registration.
3.  **Tenant Routes (Root level)**: Guarded by `tenant` middleware. Includes:
    *   Publicly accessible (but tenant-scoped) attendance clock/QR pages.
    *   Biometric enrollment endpoints.
4.  **`/admin/*`**: Tenant-specific administration (managing staff, viewing reports, approving exceptions). Guarded by `auth`, `subscription`, and `admin` middleware.
5.  **`/staff/*`**: Standard employee dashboard.

---

## 4. Developer Kickstart Guide

### Local Environment Setup & Running the System

To fully run ChronoSync Attendance locally, you need to start two main components: the Laravel PHP application (which handles web requests, queues, and frontend assets) and the Python Facial Recognition server.

#### 1. First-time Setup
1.  **Install dependencies**:
    ```bash
    composer install
    npm install
    ```
2.  **Environment Configuration**:
    Copy `.env.example` to `.env` (or run `composer run setup`). Ensure your database credentials are set (SQLite is supported out-of-the-box).
3.  **Run Migrations**:
    ```bash
    php artisan migrate --seed
    ```
4.  **Set up Python Environment**:
    The Python server handles CPU-intensive facial encoding tasks. You must install its requirements in a virtual environment.
    ```powershell
    python -m venv venv
    .\venv\Scripts\activate
    pip install -r scripts/requirements.txt # or install face_recognition, flask, etc.
    ```

#### 2. Running the Laravel Web Application
The project includes a convenient `dev` script in `composer.json` that uses `concurrently` to run everything the Laravel app needs in a single terminal.
1. Open a terminal in the root directory.
2. Run the following command:
    ```bash
    composer run dev
    ```
    *This starts the PHP development server (usually on `http://127.0.0.1:8000`), the Vite frontend bundler (`npm run dev`), the Laravel queue worker (for background jobs), and `pail` (for tailing logs).*

#### 3. Running the Python Facial Recognition Server
To use the facial recognition features (enrollment and scanning), you must run the external Python daemon alongside the Laravel app.
1. Open a **new, separate terminal** in the root directory.
2. Activate your virtual environment:
    ```powershell
    .\venv\Scripts\activate
    ```
3. Start the recognition server:
    ```powershell
    python scripts/recognition_server.py
    ```
    *This will start a Flask server listening on `http://localhost:5001`.*

> [!IMPORTANT]
> If the Python server is not running, the `FacialRecognitionService` will attempt a slower fallback via CLI, which is not recommended for production or heavy testing.

### Handling Biometric/Hardware Testing
*   **WebAuthn**: Must be tested over HTTPS or `localhost`.
*   **ZKTeco**: Check the `test_zk_api.ps1` PowerShell script in the root directory for hardware connection testing. Ensure your `.env` has `ZKTECO_ENABLED=true` and the correct `ZKTECO_DEVICE_IP`.

> [!TIP] 
> **Testing System Audits**
> You can trigger a deliberate crash to test the auditing feature by navigating to `/test-crash` in your local environment.

### Code Style & Best Practices
*   **Services**: Keep business logic in `app/Services` rather than Controllers.
*   **Scoping**: Always remember to scope queries by `tenant_id`. Rely on the `tenant` middleware and global scopes where applicable to prevent cross-tenant data leakage.

Happy Coding!
