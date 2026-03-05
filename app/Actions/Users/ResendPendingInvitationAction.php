<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\Invitation;
use App\Notifications\InviteUserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

final readonly class ResendPendingInvitationAction
{
    public function handle(Invitation $invitation, Request $request): void
    {
        if ($invitation->accepted_at !== null) {
            return;
        }

        $invitation->forceFill([
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
        ])->save();

        Notification::route('mail', $invitation->email)->notify(
            new InviteUserNotification(
                token: $invitation->token,
                tenantHost: $request->getHost(),
                tenantScheme: $request->getScheme(),
            ),
        );
    }
}
