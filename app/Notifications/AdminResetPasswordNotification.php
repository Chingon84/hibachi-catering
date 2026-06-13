<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $email = $notifiable->getEmailForPasswordReset();
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $email,
        ]);
        $expireMinutes = (int) config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject('Reset Your Hibachi Admin Password')
            ->view('emails.password-reset', [
                'resetUrl' => $url,
                'expireMinutes' => $expireMinutes,
                'email' => $email,
            ]);
    }
}
