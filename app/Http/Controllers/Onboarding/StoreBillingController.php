<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Actions\Onboarding\StoreBillingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\StoreBillingRequest;
use Illuminate\Http\RedirectResponse;

final class StoreBillingController extends Controller
{
    public function __invoke(StoreBillingRequest $request, StoreBillingAction $action): RedirectResponse
    {
        $action->handle($request);

        $request->session()->forget('onboarding_workspace_id');

        return to_route('onboarding.create-workspace')
            ->with('status', __('onboarding.messages.check_email'));
    }
}
