<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\SendContactMessageEmail;
use App\Models\ContactMessage;
use Throwable;

class ContactMessageObserver
{
    /**
     * Handle the ContactMessage "created" event.
     */
    public function created(ContactMessage $contactMessage): void
    {
        // Kuyruk sistemi çökmüş olsa bile kullanıcının formu hata vermemeli.
        // Mesaj veritabanına kaydolduysa, mail hatası sessizce loglanmalı.
        try {
            SendContactMessageEmail::dispatch($contactMessage);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
