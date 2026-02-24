<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShowSetupAccountController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        if (User::query()->exists()) {
            return to_route('login');
        }

        return Inertia::render('auth/setup-account', [
            'email' => (string) $request->query('email', ''),
        ]);
    }
}
