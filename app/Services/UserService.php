<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function updateProfile(User $user, array $data): void
    {
        $user->fill($data);

        // Eğer e-posta değişirse doğrulamayı sıfırla
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
    }

    public function deleteAccount(User $user): void
    {
        DB::transaction(function () use ($user) {
            // İleride ilişkili verileri silmek gerekirse buraya eklenir
            // (Örn: $user->subscriptions()->delete())
            $user->delete();
        });
    }
}
