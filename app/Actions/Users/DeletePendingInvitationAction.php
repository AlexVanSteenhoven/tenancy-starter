<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\Invitation;
use Illuminate\Support\Facades\Cache;

final readonly class DeletePendingInvitationAction
{
    public function handle(Invitation $invitation): void
    {
        if ($invitation->accepted_at !== null) {
            return;
        }

        $invitation->delete();

        Cache::forget('users.index');
    }
}
