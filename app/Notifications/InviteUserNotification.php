<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class InviteUserNotification extends Notification implements ShouldQueue
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
            ->subject(__('mail.invite.subject'))
            ->markdown('emails.invite-user', [
                'appName' => config('app.name'),
                'title' => __('mail.invite.title'),
                'description' => __('mail.invite.description'),
                'cta' => __('mail.invite.cta'),
                'buttonNote' => __('mail.invite.button_note'),
                'footer' => __('mail.invite.footer'),
                'inviteUrl' => $this->inviteUrl(),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }

    private function inviteUrl(): string
    {
        $path = route('invitations.accept', ['token' => $this->token], false);

        if ($this->tenantHost !== null && $this->tenantHost !== '') {
            $scheme = $this->tenantScheme ?: 'https';

            return sprintf('%s://%s%s', $scheme, $this->tenantHost, $path);
        }

        return url($path);
    }
}
