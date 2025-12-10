<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Jobs\SendVerificationEmail;

// Try to find a seeded/test user. Adjust email if needed.
$user = User::first();

if (!$user) {
    echo "No user found.\n";
    exit(1);
}

SendVerificationEmail::dispatch($user);

echo "Dispatched SendVerificationEmail for user id={$user->id}, email={$user->email}\n";
