<?php

declare(strict_types=1);

namespace App\Actions\Onboarding;

use App\Contracts\Onboarding\SubscribeWorkspaceToPlan;
use App\Models\Workspace;

final readonly class SubscribeWorkspaceToPlanAction implements SubscribeWorkspaceToPlan
{
    public function handle(Workspace $workspace, string $stripePriceId, string $paymentMethodId, int $seats): void
    {
        $workspace->createOrGetStripeCustomer();
        $workspace->newSubscription('default', $stripePriceId)
            ->quantity($seats)
            ->create($paymentMethodId);
    }
}
