<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\Invitation;
use App\Models\User;

final readonly class DeleteUserAction
{
    public function handle(User $user): void
    {
        Invitation::query()
            ->where('email', $user->email)
            ->delete();

        $user->delete();
    }
}
