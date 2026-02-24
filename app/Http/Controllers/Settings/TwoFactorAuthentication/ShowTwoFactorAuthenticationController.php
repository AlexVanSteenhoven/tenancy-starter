<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings\TwoFactorAuthentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

final class ShowTwoFactorAuthenticationController extends Controller implements HasMiddleware
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(TwoFactorAuthenticationRequest $request): Response
    {
        $request->ensureStateIsValid();

        return Inertia::render('settings/two-factor', [
            'twoFactorEnabled' => $request->user()->hasEnabledTwoFactorAuthentication(),
            'requiresConfirmation' => Features::optionEnabled(
                feature: Features::twoFactorAuthentication(),
                option: 'confirm'
            ),
        ]);
    }

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return Features::optionEnabled(
            feature: Features::twoFactorAuthentication(),
            option: 'confirmPassword'
        ) ? [new Middleware('password.confirm')] : [];
    }
}
