<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Actions\Onboarding\StoreSetupAccountAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\StoreSetupAccountRequest;
use Illuminate\Http\RedirectResponse;

final class StoreSetupAccountController extends Controller
{
    public function __invoke(StoreSetupAccountRequest $request, StoreSetupAccountAction $action): RedirectResponse
    {
        $action->handle($request);

        return to_route('dashboard');
    }
}
