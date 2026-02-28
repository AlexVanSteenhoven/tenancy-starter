<?php

declare(strict_types=1);

namespace App\Actions\Invitations;

use App\Http\Requests\Invitations\StoreAcceptInvitationRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

final readonly class AcceptInvitationAction
{
    public function handle(StoreAcceptInvitationRequest $request): void
    {
        $invitation = $request->invitation();

        if ($invitation === null || $invitation->isAccepted() || $invitation->isExpired()) {
            return;
        }

        $user = User::query()->create([
            'name' => mb_trim((string) $request->input('name')),
            'email' => $invitation->email,
            'password' => (string) $request->input('password'),
        ]);

        $guardName = (string) config('auth.defaults.guard', 'web');

        Role::findOrCreate($invitation->role, $guardName);
        $user->assignRole($invitation->role);

        $invitation->forceFill([
            'accepted_at' => now(),
        ])->save();

        $user->sendEmailVerificationNotification();

        Auth::login($user);
    }
}
