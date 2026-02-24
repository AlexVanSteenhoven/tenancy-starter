<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $token,
        private readonly ?string $tenantHost = null,
        private readonly ?string $tenantScheme = null,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('mail.password_reset.subject'))
            ->markdown('emails.password-reset', [
                'appName' => config('app.name'),
                'title' => __('mail.password_reset.title'),
                'description' => __('mail.password_reset.description'),
                'cta' => __('mail.password_reset.cta'),
                'buttonNote' => __('mail.password_reset.button_note'),
                'footer' => __('mail.password_reset.footer'),
                'resetUrl' => $this->resetUrl($notifiable),
            ]);
    }

    private function resetUrl(object $notifiable): string
    {
        $path = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false);

        if ($this->tenantHost !== null && $this->tenantHost !== '') {
            $scheme = $this->tenantScheme ?: 'https';

            return sprintf('%s://%s%s', $scheme, $this->tenantHost, $path);
        }

        return url($path);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
