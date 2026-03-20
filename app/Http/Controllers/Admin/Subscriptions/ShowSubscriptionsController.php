<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Subscriptions;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Workspace;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Cashier\Subscription;

final class ShowSubscriptionsController extends Controller
{
    public function __invoke(): Response
    {
        $plansByStripePrice = Plan::query()
            ->whereNotNull('stripe_price_id')
            ->get(['name', 'price_monthly', 'stripe_price_id'])
            ->keyBy('stripe_price_id');

        $subscriptions = Subscription::query()
            ->latest()
            ->get()
            ->map(function (Subscription $subscription) use ($plansByStripePrice): array {
                $workspace = Workspace::query()
                    ->with('domains')
                    ->find($subscription->workspace_id);
                $plan = $subscription->stripe_price !== null
                    ? $plansByStripePrice->get($subscription->stripe_price)
                    : null;

                return [
                    'id' => $subscription->id,
                    'workspace_id' => $subscription->workspace_id,
                    'workspace_name' => $workspace?->name,
                    'workspace_domain' => $workspace?->domains->first()?->domain,
                    'type' => $subscription->type,
                    'stripe_id' => $subscription->stripe_id,
                    'stripe_status' => $subscription->stripe_status,
                    'stripe_price' => $subscription->stripe_price,
                    'plan_name' => $plan?->name,
                    'plan_price_monthly' => $plan?->price_monthly,
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
