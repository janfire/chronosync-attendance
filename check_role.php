<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', 'masiyam@zou.ac.zw')->first();
if ($user) {
    echo "Role Type: " . gettype($user->role) . "\n";
    if ($user->role instanceof UnitEnum) {
        echo "Role Value: " . $user->role->value . "\n";
    } else {
        echo "Role Value: " . $user->role . "\n";
    }
    echo "isPlatformAdmin: " . ($user->isPlatformAdmin() ? 'true' : 'false') . "\n";
} else {
    echo "User not found\n";
}
