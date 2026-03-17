<?php

declare(strict_types=1);

namespace App\Contracts\Onboarding;

use App\Models\Workspace;

interface SubscribeWorkspaceToPlan
{
    public function handle(Workspace $workspace, string $stripePriceId, string $paymentMethodId, int $seats): void;
}
