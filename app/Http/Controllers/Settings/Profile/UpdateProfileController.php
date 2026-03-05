<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings\Profile;

use App\Actions\Settings\ProfileUpdateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;

final class UpdateProfileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ProfileUpdateRequest $request, ProfileUpdateAction $action): RedirectResponse
    {
        $action->handle(
            request: $request
        );

        return to_route('settings.profile.edit')
            ->with('status', __('settings.notifications.profile.title'))
            ->with('statusDescription', __('settings.notifications.profile.description'));
    }
}
