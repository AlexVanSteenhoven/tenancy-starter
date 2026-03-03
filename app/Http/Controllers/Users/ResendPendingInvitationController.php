<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Actions\Users\ResendPendingInvitationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\ResendPendingInvitationRequest;
use App\Models\Invitation;
use Illuminate\Http\RedirectResponse;

final class ResendPendingInvitationController extends Controller
{
    public function __invoke(
        ResendPendingInvitationRequest $request,
        string $invitation,
        ResendPendingInvitationAction $action,
    ): RedirectResponse {
        $pendingInvitation = Invitation::query()->findOrFail($invitation);

        $action->handle($pendingInvitation, $request);

        return back()
            ->with('status', __('notifications.users.resend.title'))
            ->with('statusDescription', __('notifications.users.resend.description'));
    }
}
