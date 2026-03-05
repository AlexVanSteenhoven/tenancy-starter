<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Actions\Onboarding\StoreOnboardingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\StoreOnboardingRequest;
use Illuminate\Http\RedirectResponse;

final class StoreOnboardingController extends Controller
{
    public function __invoke(StoreOnboardingRequest $request, StoreOnboardingAction $action): RedirectResponse
    {
        $action->handle($request);

        return to_route('onboarding.create-workspace')
            ->with('status', __('onboarding.messages.check_email'));
    }
}
