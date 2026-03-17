<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Workspaces;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Workspace;
use Inertia\Inertia;
use Inertia\Response;

final class ShowWorkspaceController extends Controller
{
    public function __invoke(Workspace $workspace): Response
    {
        $workspace->load([
            'domains',
            'billingPlan',
            'subscriptions' => fn ($query) => $query->latest()->limit(1),
        ]);

        $availablePlans = Plan::query()
            ->where('is_active', true)
            ->orderBy('price_monthly')
            ->get(['slug', 'name', 'price_monthly'])
            ->map(fn (Plan $plan): array => [
                'slug' => $plan->slug,
                'name' => $plan->name,
                'price_monthly' => $plan->price_monthly,
            ]);

        return Inertia::render('admin/workspaces/show', [
            'workspace' => [
                'id' => (string) $workspace->id,
                'name' => $workspace->name,
                'plan' => $workspace->plan,
                'domain' => $workspace->domains->first()?->domain,
                'stripe_id' => $workspace->stripe_id,
                'subscription' => [
                    'id' => $workspace->subscriptions->first()?->id,
                    'stripe_id' => $workspace->subscriptions->first()?->stripe_id,
                    'status' => $workspace->subscriptions->first()?->stripe_status,
                    'stripe_price' => $workspace->subscriptions->first()?->stripe_price,
                    'ends_at' => $workspace->subscriptions->first()?->ends_at?->toDateTimeString(),
                ],
            ],
            'availablePlans' => $availablePlans,
        ]);
    }
}
