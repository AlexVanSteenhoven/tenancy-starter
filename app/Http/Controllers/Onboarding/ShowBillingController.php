<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShowBillingController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $workspace = Workspace::query()
            ->with('domains')
            ->find($request->session()->get('onboarding_workspace_id'));

        if (! $workspace instanceof Workspace) {
            $request->session()->forget('onboarding_workspace_id');

            return to_route('onboarding.create-workspace');
        }

        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('price_monthly')
            ->get()
            ->map(fn (Plan $plan): array => [
                'slug' => $plan->slug,
                'name' => $plan->name,
                'description' => $plan->description,
                'price_monthly' => $plan->price_monthly,
                'features' => $plan->features ?? [],
            ])
            ->values();

        return Inertia::render('onboarding/billing', [
            'workspace' => [
                'name' => $workspace->name,
                'domain' => (string) ($workspace->domains->first()?->domain ?? ''),
            ],
            'plans' => $plans,
            'stripeKey' => config('cashier.key'),
        ]);
    }
}
