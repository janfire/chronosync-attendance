<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ZKTeco Device Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your ZKTeco fingerprint device connection settings.
    |
    */

    'enabled' => env('ZKTECO_ENABLED', false),

    'device_ip' => env('ZKTECO_DEVICE_IP', '192.168.1.100'),

    'device_port' => env('ZKTECO_DEVICE_PORT', 4370),

    /*
    |--------------------------------------------------------------------------
    | Sync Configuration
    |--------------------------------------------------------------------------
    */

    // How often to sync attendance logs from device (minutes)
    'sync_interval' => env('ZKTECO_SYNC_INTERVAL', 5),

    // Auto-sync enabled
    'auto_sync' => env('ZKTECO_AUTO_SYNC', true),

    /*
    |--------------------------------------------------------------------------
    | Fingerprint Settings
    |--------------------------------------------------------------------------
    */

    // Allow WebAuthn (browser-based) fingerprint
    'allow_webauthn' => env('ALLOW_WEBAUTHN_FINGERPRINT', true),

    // Allow ZKTeco device fingerprint
    'allow_zkteco' => env('ALLOW_ZKTECO_FINGERPRINT', true),

    // Require both facial and fingerprint enrollment
    'require_both_biometrics' => env('REQUIRE_BOTH_BIOMETRICS', false),

];
