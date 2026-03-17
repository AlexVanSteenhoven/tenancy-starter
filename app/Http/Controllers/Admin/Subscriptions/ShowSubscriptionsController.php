<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Subscriptions;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Cashier\Subscription;

final class ShowSubscriptionsController extends Controller
{
    public function __invoke(): Response
    {
        $subscriptions = Subscription::query()
            ->latest()
            ->get()
            ->map(function (Subscription $subscription): array {
                $workspace = Workspace::query()
                    ->with('domains')
                    ->find($subscription->workspace_id);

                return [
                    'id' => $subscription->id,
                    'workspace_id' => $subscription->workspace_id,
                    'workspace_name' => $workspace?->name,
                    'workspace_domain' => $workspace?->domains->first()?->domain,
                    'type' => $subscription->type,
                    'stripe_id' => $subscription->stripe_id,
                    'stripe_status' => $subscription->stripe_status,
                    'stripe_price' => $subscription->stripe_price,
                    'quantity' => $subscription->quantity,
                    'trial_ends_at' => $subscription->trial_ends_at?->toDateTimeString(),
                    'ends_at' => $subscription->ends_at?->toDateTimeString(),
                    'created_at' => $subscription->created_at?->toDateTimeString(),
                ];
            });

        return Inertia::render('admin/subscriptions/index', [
            'subscriptions' => $subscriptions,
        ]);
    }
}
