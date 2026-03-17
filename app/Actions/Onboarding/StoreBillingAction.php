<?php

declare(strict_types=1);

namespace App\Actions\Onboarding;

use App\Contracts\Onboarding\SubscribeWorkspaceToPlan;
use App\Http\Requests\Onboarding\StoreBillingRequest;
use App\Models\Plan;
use App\Models\Workspace;
use App\Notifications\WorkspaceReadyMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

final readonly class StoreBillingAction
{
    public function __construct(private SubscribeWorkspaceToPlan $subscribeWorkspaceToPlan) {}

    public function handle(StoreBillingRequest $request): void
    {
        $workspace = Workspace::query()
            ->with('domains')
            ->find($request->session()->get('onboarding_workspace_id'));

        if (! $workspace instanceof Workspace) {
            throw ValidationException::withMessages([
                'workspace' => __('onboarding.billing.validation.workspace_missing'),
            ]);
        }

        $plan = Plan::query()
            ->where('slug', $request->string('plan')->toString())
            ->where('is_active', true)
            ->first();

        if (! $plan instanceof Plan) {
            throw ValidationException::withMessages([
                'plan' => __('onboarding.billing.validation.plan_unavailable'),
            ]);
        }

        DB::transaction(function () use ($request, $workspace, $plan): void {
            $workspace->plan = $plan->slug;
            $workspace->save();

            if ($plan->price_monthly > 0) {
                if ($plan->stripe_price_id === null) {
                    throw ValidationException::withMessages([
                        'plan' => __('onboarding.billing.validation.plan_unavailable'),
                    ]);
                }

                $this->subscribeWorkspaceToPlan->handle(
                    workspace: $workspace,
                    stripePriceId: $plan->stripe_price_id,
                    paymentMethodId: $request->string('payment_method')->toString(),
                    seats: $request->integer('seats'),
                );
            }

            $onboardingEmail = (string) ($workspace->onboarding_email ?? '');
            $workspaceDomain = (string) ($workspace->domains->first()?->domain ?? '');

            if ($onboardingEmail !== '' && $workspaceDomain !== '') {
                Notification::route(channel: 'mail', route: $onboardingEmail)
                    ->notify(new WorkspaceReadyMail(
                        workspaceName: $workspace->name,
                        workspaceDomain: $workspaceDomain,
                        email: $onboardingEmail,
                    ));

                unset($workspace->onboarding_email);
                $workspace->save();
            }
        });
    }
}
