<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContactMessage;

class ContactService
{
    public function storeMessage(array $data, string $ip, ?string $userAgent): void
    {
        ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => $data['message'],
            'ip' => $ip,
            'user_agent' => $userAgent,
            'handled' => false,
        ]);
    }
}
