<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = \App\Models\BiometricData::where('facial_status', 'captured')->whereNotNull('facial_encoding')->count();
echo "Total enrolled faces: " . $count . "\n";
