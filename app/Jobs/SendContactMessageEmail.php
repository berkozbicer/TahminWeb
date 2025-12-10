<?php

namespace App\Jobs;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendContactMessageEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ContactMessage $messageModel;

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
        $body = "Yeni iletişim formu mesajı:\n\n";
        $body .= "İsim: " . $this->messageModel->name . "\n";
        $body .= "E-posta: " . $this->messageModel->email . "\n\n";
        $body .= "Mesaj:\n" . $this->messageModel->message . "\n";

        Mail::raw($body, function ($message) {
            $message->to(config('mail.from.address'))
                ->subject('Yeni İletişim Formu Mesajı')
                ->replyTo($this->messageModel->email, $this->messageModel->name);
        });
    }
}
