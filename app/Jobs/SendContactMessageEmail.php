<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendContactMessageEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ContactMessage $messageModel;

    /**
     * Job kaç kez denensin? (Hata alırsa)
     */
    public int $tries = 3;

    /**
     * Tekrar denemeden önce kaç saniye beklesin?
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(ContactMessage $messageModel)
    {
        $this->messageModel = $messageModel;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // E-posta içeriği
            $body = sprintf(
                "Yeni iletişim formu mesajı:\n\nİsim: %s\nE-posta: %s\n\nMesaj:\n%s",
                $this->messageModel->name,
                $this->messageModel->email,
                $this->messageModel->message
            );

            // Yöneticiye (Config'den gelen mail) gönder
            Mail::raw($body, function ($message) {
                $to = config('mail.from.address');

                if (!$to) {
                    throw new \Exception('Admin mail adresi (MAIL_FROM_ADDRESS) ayarlanmamış.');
                }

                $message->to($to)
                    ->subject('Yeni İletişim Formu Mesajı: ' . $this->messageModel->name)
                    ->replyTo($this->messageModel->email, $this->messageModel->name);
            });

            Log::info("İletişim mesajı maili gönderildi. ID: " . $this->messageModel->id);

        } catch (Throwable $e) {
            Log::error("İletişim maili gönderilemedi. Hata: " . $e->getMessage());

            // Hatayı tekrar fırlat ki Queue worker tekrar denesin (tries > 1 ise)
            throw $e;
        }
    }
}
