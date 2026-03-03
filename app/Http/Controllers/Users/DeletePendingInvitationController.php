<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Actions\Users\DeletePendingInvitationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\DeletePendingInvitationRequest;
use App\Models\Invitation;
use Illuminate\Http\RedirectResponse;

final class DeletePendingInvitationController extends Controller
{
    public function __invoke(
        DeletePendingInvitationRequest $request,
        string $invitation,
        DeletePendingInvitationAction $action,
    ): RedirectResponse {
        $pendingInvitation = Invitation::query()->findOrFail($invitation);

        $action->handle($pendingInvitation);

        return back()
            ->with('status', __('notifications.users.invitation-delete.title'))
            ->with('statusDescription', __('notifications.users.invitation-delete.description'));
    }
}
