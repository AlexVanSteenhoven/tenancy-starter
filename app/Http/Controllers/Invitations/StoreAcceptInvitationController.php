<?php

declare(strict_types=1);

namespace App\Http\Controllers\Invitations;

use App\Actions\Invitations\AcceptInvitationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invitations\StoreAcceptInvitationRequest;
use Illuminate\Http\RedirectResponse;

final class StoreAcceptInvitationController extends Controller
{
    public function __invoke(StoreAcceptInvitationRequest $request, AcceptInvitationAction $action): RedirectResponse
    {
        $action->handle($request);

        return to_route('dashboard');
    }
}
