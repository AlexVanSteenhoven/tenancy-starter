<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShowOnboardingController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render(
            component: 'onboarding/create-workspace',
            props: [
                'status' => $request->session()->get(key: 'status'),
            ]
        );
    }
}
