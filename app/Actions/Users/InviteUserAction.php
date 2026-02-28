<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Http\Requests\Users\InviteUserRequest;
use App\Models\Invitation;
use App\Notifications\InviteUserNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

final readonly class InviteUserAction
{
    public function handle(InviteUserRequest $request): void
    {
        $invitation = Invitation::query()->create([
            'email' => mb_strtolower(mb_trim((string) $request->input('email'))),
            'role' => (string) $request->input('role'),
            'token' => Str::random(64),
            'invited_by_id' => (string) $request->user()->id,
            'expires_at' => now()->addDays(7),
        ]);

        Notification::route('mail', $invitation->email)->notify(
            new InviteUserNotification(
                token: $invitation->token,
                tenantHost: $request->getHost(),
                tenantScheme: $request->getScheme(),
            ),
        );
    }
}
