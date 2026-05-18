<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ZOU Website URL
    |--------------------------------------------------------------------------
    |
    | The URL to redirect users to after successful clock in/out
    |
    */
    'zou_website_url' => env('ZOU_WEBSITE_URL', 'https://www.zou.ac.zw'),

    /*
    |--------------------------------------------------------------------------
    | Redirect Delay (seconds)
    |--------------------------------------------------------------------------
    |
    | How many seconds to wait before redirecting after clock in/out
    |
    */
    'redirect_delay' => env('ATTENDANCE_REDIRECT_DELAY', 2),
];

