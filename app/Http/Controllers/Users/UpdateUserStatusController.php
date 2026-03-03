<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Actions\Users\UpdateUserStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateUserStatusRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

final class UpdateUserStatusController extends Controller
{
    public function __invoke(UpdateUserStatusRequest $request, string $user, UpdateUserStatusAction $action): RedirectResponse
    {
        $targetUser = User::query()->findOrFail($user);

        $action->handle($request, $targetUser);

        return back()
            ->with('status', __('notifications.users.status-update.title'))
            ->with('statusDescription', __('notifications.users.status-update.description'));
    }
}
