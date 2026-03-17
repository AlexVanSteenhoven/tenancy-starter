<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Http\Requests\Admin\UpdateWorkspaceSubscriptionRequest;
use App\Models\Plan;
use App\Models\Workspace;
use Illuminate\Validation\ValidationException;

final readonly class UpdateWorkspaceSubscriptionAction
{
    public function handle(UpdateWorkspaceSubscriptionRequest $request, Workspace $workspace): void
    {
        $plan = Plan::query()
            ->where('slug', $request->string('plan')->toString())
            ->where('is_active', true)
            ->first();

        if (! $plan instanceof Plan) {
            throw ValidationException::withMessages([
                'plan' => __('admin.workspaces.validation.plan_not_found'),
            ]);
        }

        $workspace->plan = $plan->slug;
        $workspace->save();

        if ($plan->price_monthly === 0) {
            $workspace->subscription('default')?->cancel();

            return;
        }

        if ($plan->stripe_price_id === null) {
            throw ValidationException::withMessages([
                'plan' => __('admin.workspaces.validation.plan_missing_price'),
            ]);
        }

        $currentSubscription = $workspace->subscription('default');

        if ($currentSubscription === null) {
            if (! $workspace->hasDefaultPaymentMethod()) {
                throw ValidationException::withMessages([
                    'plan' => __('admin.workspaces.validation.missing_default_payment_method'),
                ]);
            }

            $workspace->newSubscription('default', $plan->stripe_price_id)
                ->create((string) $workspace->defaultPaymentMethod()?->id);

            return;
        }

        $currentSubscription->swap($plan->stripe_price_id);
    }
}
