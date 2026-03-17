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
        $workspace = $action->handle($request);

        $request->session()->put('onboarding_workspace_id', (string) $workspace->id);

        return to_route('onboarding.billing');
    }
}
