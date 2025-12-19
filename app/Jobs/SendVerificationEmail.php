<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendVerificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public User $user;

    /**
     * Başarısız olursa en fazla kaç kere denensin?
     */
    public int $tries = 3;

    /**
     * Hata aldığında kaç saniye bekleyip tekrar denesin?
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Kullanıcı silinmişse işlemi iptal et (Boşa kaynak harcama)
            if (!$this->user) {
                Log::warning("Doğrulama maili atılacak kullanıcı bulunamadı.");
                return;
            }

            $this->user->sendEmailVerificationNotification();

            Log::info("Doğrulama maili kuyruktan gönderildi. User ID: " . $this->user->id);

        } catch (Throwable $e) {
            // Detaylı loglama
            Log::error('Verification email job failed for user id ' . $this->user->id . ': ' . $e->getMessage());

            // ÖNEMLİ: Hatayı tekrar fırlatıyoruz.
            // Bunu yapmazsak Laravel işin başarıyla bittiğini sanır ve tekrar denemez!
            throw $e;
        }
    }
}
