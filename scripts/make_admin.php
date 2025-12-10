<?php
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Foundation\Application;

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = \App\Models\User::first();
    if (! $user) {
        echo "NO_USER\n";
        exit(1);
    }
    $user->role = 'admin';
    $user->save();
    echo "OK: user id={$user->id}\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
