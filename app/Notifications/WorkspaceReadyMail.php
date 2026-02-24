<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class WorkspaceReadyMail extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $workspaceName,
        public string $subdomain,
        public string $email,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $centralDomain = collect(config('tenancy.central_domains'))->first() ?? config('tenancy.central_domains.0');

        $setupUrl = sprintf(
            'https://%s.%s/onboarding/account/create?email=%s',
            $this->subdomain,
            $centralDomain,
            urlencode($this->email),
        );

        return (new MailMessage)
            ->subject(__('mail.workspace.ready.subject'))
            ->markdown('emails.workspace-ready', [
                'appName' => config('app.name'),
                'workspaceName' => $this->workspaceName,
                'setupUrl' => $setupUrl,
                'title' => __('mail.workspace.ready.title', ['workspace' => $this->workspaceName]),
                'description' => __('mail.workspace.ready.description'),
                'cta' => __('mail.workspace.ready.cta'),
                'buttonNote' => __('mail.workspace.ready.button_note'),
                'footer' => __('mail.workspace.ready.footer'),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
