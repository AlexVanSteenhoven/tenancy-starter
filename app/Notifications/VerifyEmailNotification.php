<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

final class VerifyEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
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
            ->subject(__('mail.email_verification.subject'))
            ->markdown('emails.verify-email', [
                'appName' => config('app.name'),
                'title' => __('mail.email_verification.title'),
                'description' => __('mail.email_verification.description'),
                'cta' => __('mail.email_verification.cta'),
                'buttonNote' => __('mail.email_verification.button_note'),
                'footer' => __('mail.email_verification.footer'),
                'verificationUrl' => $this->verificationUrl($notifiable),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }

    private function verificationUrl(object $notifiable): string
    {
        $expiration = Carbon::now()->addMinutes(config('auth.verification.expire', 60));
        $parameters = [
            'id' => $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification()),
        ];

        if ($this->tenantHost === null || $this->tenantHost === '') {
            return URL::temporarySignedRoute('verification.verify', $expiration, $parameters);
        }

        $fallbackAppUrl = (string) config('app.url');
        $fallbackScheme = parse_url($fallbackAppUrl, PHP_URL_SCHEME) ?: 'https';
        $tenantRootUrl = sprintf('%s://%s', $this->tenantScheme ?: $fallbackScheme, $this->tenantHost);

        URL::forceRootUrl($tenantRootUrl);
        URL::forceScheme($this->tenantScheme ?: (string) parse_url($tenantRootUrl, PHP_URL_SCHEME));

        try {
            return URL::temporarySignedRoute('verification.verify', $expiration, $parameters);
        } finally {
            URL::forceRootUrl($fallbackAppUrl);
            URL::forceScheme((string) $fallbackScheme);
        }
    }
}
