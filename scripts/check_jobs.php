<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$jobs = DB::table('jobs')->count();
$failed = DB::table('failed_jobs')->count();

echo "jobs={$jobs} failed_jobs={$failed}\n";
