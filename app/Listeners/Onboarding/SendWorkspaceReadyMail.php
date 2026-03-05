<?php

declare(strict_types=1);

namespace App\Listeners\Onboarding;

use App\Models\Workspace;
use App\Notifications\WorkspaceReadyMail as WorkspaceReadyNotification;
use Illuminate\Support\Facades\Notification;
use Stancl\Tenancy\Events\DatabaseSeeded;

final class SendWorkspaceReadyMail
{
    public function handle(DatabaseSeeded $event): void
    {
        $workspace = Workspace::query()
            ->with('domains')
            ->find($event->tenant->getTenantKey());

        if (! $workspace instanceof Workspace) {
            return;
        }

        $email = (string) ($workspace->onboarding_email ?? '');
        $workspaceDomain = (string) ($workspace->domains->first()?->domain ?? '');

        if ($email === '' || $workspaceDomain === '') {
            return;
        }

        Notification::route(channel: 'mail', route: $email)
            ->notify(new WorkspaceReadyNotification(
                $workspace->name,
                $workspaceDomain,
                $email,
            ));

        unset($workspace->onboarding_email);
        $workspace->save();
    }
}
