<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Actions\Users\InviteUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\InviteUserRequest;
use Illuminate\Http\RedirectResponse;

final class InviteUserController extends Controller
{
    public function __invoke(InviteUserRequest $request, InviteUserAction $action): RedirectResponse
    {
        $action->handle($request);

        return back()->with('status', __('users.invite.messages.sent'));
    }
}
