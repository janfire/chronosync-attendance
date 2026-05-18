<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Hardcoded Admin Credentials
    |--------------------------------------------------------------------------
    |
    | This admin account is always available and does not require database
    | registration. It will be automatically created in the database on first login.
    |
    | IMPORTANT: Change these credentials in production!
    |
    */
    'hardcoded_admin' => [
        'email' => env('ADMIN_EMAIL', 'admin@zou.ac.zw'),
        'password' => env('ADMIN_PASSWORD', 'admin123'),
        'name' => env('ADMIN_NAME', 'System Administrator'),
        'employee_number' => env('ADMIN_EMPLOYEE_NUMBER', 'ADMIN001'),
        'role' => 'admin',
    ],
];

