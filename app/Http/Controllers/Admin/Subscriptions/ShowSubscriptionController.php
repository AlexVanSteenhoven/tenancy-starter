<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Subscriptions;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Services\Stripe\StripeAdminService;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Cashier\Subscription;

final class ShowSubscriptionController extends Controller
{
    public function __invoke(Subscription $subscription, StripeAdminService $stripeAdminService): Response
    {
        $workspace = Workspace::query()
            ->with('domains')
            ->find($subscription->workspace_id);

        $stripeSubscription = $stripeAdminService->retrieveSubscription($subscription->stripe_id);

        return Inertia::render('admin/subscriptions/show', [
            'subscription' => [
                'id' => $subscription->id,
                'workspace_name' => $workspace?->name,
                'workspace_domain' => $workspace?->domains->first()?->domain,
                'stripe_id' => $subscription->stripe_id,
                'stripe_status' => $subscription->stripe_status,
                'stripe_price' => $subscription->stripe_price,
                'quantity' => $subscription->quantity,
                'trial_ends_at' => $subscription->trial_ends_at?->toDateTimeString(),
                'ends_at' => $subscription->ends_at?->toDateTimeString(),
                'created_at' => $subscription->created_at?->toDateTimeString(),
                'stripe_current_period_start' => $stripeSubscription->current_period_start,
                'stripe_current_period_end' => $stripeSubscription->current_period_end,
                'stripe_cancel_at_period_end' => $stripeSubscription->cancel_at_period_end,
            ],
        ]);
    }
}
