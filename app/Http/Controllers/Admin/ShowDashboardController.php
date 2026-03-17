<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use Inertia\Inertia;
use Inertia\Response;

final class ShowDashboardController extends Controller
{
    public function __invoke(): Response
    {
        $workspaces = Workspace::query()
            ->with(['billingPlan', 'domains'])
            ->withCount('subscriptions')
            ->latest()
            ->limit(5)
            ->get();

        $activeSubscriptionStatuses = ['active', 'trialing', 'past_due'];
        $totalActiveSubscriptions = Workspace::query()
            ->whereHas('subscriptions', fn ($query) => $query->whereIn('stripe_status', $activeSubscriptionStatuses))
            ->count();

        $mrrInCents = Workspace::query()
            ->with(['billingPlan', 'subscriptions' => fn ($query) => $query->whereIn('stripe_status', $activeSubscriptionStatuses)])
            ->get()
            ->sum(fn (Workspace $workspace): int => $workspace->subscriptions->isNotEmpty()
                ? (int) ($workspace->billingPlan?->price_monthly ?? 0)
                : 0);

        return Inertia::render('admin/dashboard', [
            'stats' => [
                'totalWorkspaces' => Workspace::query()->count(),
                'totalActiveSubscriptions' => $totalActiveSubscriptions,
                'mrrInCents' => $mrrInCents,
            ],
            'recentWorkspaces' => $workspaces->map(fn (Workspace $workspace): array => [
                'id' => (string) $workspace->id,
                'name' => $workspace->name,
                'plan' => $workspace->plan,
                'domain' => $workspace->domains->first()?->domain,
                'created_at' => $workspace->created_at?->toDateTimeString(),
            ]),
        ]);
    }
}
