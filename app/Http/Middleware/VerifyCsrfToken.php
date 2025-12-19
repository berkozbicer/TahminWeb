<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // PayTR'dan gelen POST isteklerinde CSRF token aranmaz (Webhook)
        'paytr/*',   // /paytr/callback, /paytr/bildirim vs. hepsini kapsar
        'odeme/*',   // Eğer rotayı /odeme/callback yaptıysak bunu da kapsasın
    ];
}
