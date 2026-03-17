<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Workspaces;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use Inertia\Inertia;
use Inertia\Response;

final class ShowWorkspacesController extends Controller
{
    public function __invoke(): Response
    {
        $workspaces = Workspace::query()
            ->with([
                'domains',
                'billingPlan',
                'subscriptions' => fn ($query) => $query->latest()->limit(1),
            ])
            ->latest()
            ->get()
            ->map(fn (Workspace $workspace): array => [
                'id' => (string) $workspace->id,
                'name' => $workspace->name,
                'domain' => $workspace->domains->first()?->domain,
                'plan' => $workspace->plan,
                'plan_name' => $workspace->billingPlan?->name,
                'subscription_status' => $workspace->subscriptions->first()?->stripe_status,
                'stripe_id' => $workspace->stripe_id,
                'created_at' => $workspace->created_at?->toDateTimeString(),
            ]);

        return Inertia::render('admin/workspaces/index', [
            'workspaces' => $workspaces,
        ]);
    }
}
