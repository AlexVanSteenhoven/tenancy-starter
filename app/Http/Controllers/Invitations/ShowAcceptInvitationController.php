<?php

declare(strict_types=1);

namespace App\Http\Controllers\Invitations;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Inertia\Inertia;
use Inertia\Response;

final class ShowAcceptInvitationController extends Controller
{
    public function __invoke(string $token): Response
    {
        $invitation = Invitation::query()
            ->with('invitedBy:id,name')
            ->where('token', $token)
            ->first();

        $isValid = $invitation !== null && ! $invitation->isAccepted() && ! $invitation->isExpired();

        return Inertia::render('invitations/accept-invitation', [
            'token' => $token,
            'invitation' => $isValid && $invitation !== null
                ? [
                    'email' => $invitation->email,
                    'role' => $invitation->role,
                    'invitedBy' => $invitation->invitedBy?->name,
                ]
                : null,
        ]);
    }
}
