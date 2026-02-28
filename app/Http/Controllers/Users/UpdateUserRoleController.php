<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Actions\Users\UpdateUserRoleAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

final class UpdateUserRoleController extends Controller
{
    public function __invoke(UpdateUserRoleRequest $request, string $user, UpdateUserRoleAction $action): RedirectResponse
    {
        $targetUser = User::query()->findOrFail($user);

        $action->handle($request, $targetUser);

        return back()->with('status', __('users.messages.role_updated'));
    }
}
