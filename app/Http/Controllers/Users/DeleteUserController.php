<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Actions\Users\DeleteUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\DeleteUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

final class DeleteUserController extends Controller
{
    public function __invoke(DeleteUserRequest $request, string $user, DeleteUserAction $action): RedirectResponse
    {
        $targetUser = User::query()->findOrFail($user);

        $action->handle($targetUser);

        return back()->with('status', __('users.messages.deleted'));
    }
}
