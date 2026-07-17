<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail
{
    protected function verificationUrl($notifiable): string
    {
        $relativeUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes((int) config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
            false,
        );

        return rtrim((string) config('app.url'), '/').$relativeUrl;
    }

    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifikasi Email Z-Airlines')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Terima kasih telah mendaftar di Z-Airlines.')
            ->line('Klik tombol di bawah untuk memverifikasi alamat email sebelum menggunakan fitur booking dan pembayaran.')
            ->action('Verifikasi Email', $verificationUrl)
            ->line('Tautan ini hanya berlaku sementara. Jika tautan tidak dapat digunakan, masuk kembali lalu kirim ulang email verifikasi.')
            ->salutation('Salam, Tim Z-Airlines');
    }
}
