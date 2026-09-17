<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
echo "Client ID: " . config('services.google.client_id') . "\n";
echo "Secret: " . config('services.google.client_secret') . "\n";
echo "Redirect: " . config('services.google.redirect') . "\n";
