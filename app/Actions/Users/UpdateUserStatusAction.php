<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Http\Requests\Users\UpdateUserStatusRequest;
use App\Models\User;

final readonly class UpdateUserStatusAction
{
    public function handle(UpdateUserStatusRequest $request, User $user): void
    {
        $user->forceFill([
            'status' => (string) $request->input('status'),
        ])->save();
    }
}
