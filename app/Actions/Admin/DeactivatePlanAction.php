<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Plan;
use App\Services\Stripe\StripeAdminService;

final readonly class DeactivatePlanAction
{
    public function __construct(private StripeAdminService $stripeAdminService) {}

    public function handle(Plan $plan): void
    {
        if ($plan->stripe_price_id !== null) {
            $this->stripeAdminService->deactivatePrice($plan->stripe_price_id);
        }

        if ($plan->stripe_product_id !== null) {
            $this->stripeAdminService->updateProduct(
                productId: $plan->stripe_product_id,
                name: $plan->name,
                description: $plan->description,
                isActive: false,
            );
        }

        $plan->update([
            'is_active' => false,
        ]);
    }
}
