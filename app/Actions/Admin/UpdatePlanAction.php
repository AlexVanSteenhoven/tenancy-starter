<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Plan;
use App\Services\Stripe\StripeAdminService;

final readonly class UpdatePlanAction
{
    public function __construct(private StripeAdminService $stripeAdminService) {}

    public function handle(UpdatePlanRequest $request, Plan $plan): void
    {
        $validatedData = $request->validated();

        if ($plan->stripe_product_id !== null) {
            $this->stripeAdminService->updateProduct(
                productId: $plan->stripe_product_id,
                name: (string) $validatedData['name'],
                description: $validatedData['description'] ?? null,
                isActive: (bool) $validatedData['is_active'],
            );
        }

        $nextStripePriceId = $plan->stripe_price_id;
        $nextPriceMonthly = (int) $validatedData['price_monthly'];

        if ($plan->stripe_product_id !== null && $nextPriceMonthly > 0 && $nextPriceMonthly !== $plan->price_monthly) {
            if ($plan->stripe_price_id !== null) {
                $this->stripeAdminService->deactivatePrice($plan->stripe_price_id);
            }

            $price = $this->stripeAdminService->createMonthlyPrice(
                productId: $plan->stripe_product_id,
                amountInCents: $nextPriceMonthly,
            );

            $nextStripePriceId = $price->id;
        }

        if ($nextPriceMonthly === 0 && $plan->stripe_price_id !== null) {
            $this->stripeAdminService->deactivatePrice($plan->stripe_price_id);
            $nextStripePriceId = null;
        }

        $plan->update([
            'slug' => $validatedData['slug'],
            'name' => $validatedData['name'],
            'description' => $validatedData['description'] ?? null,
            'price_monthly' => $nextPriceMonthly,
            'stripe_price_id' => $nextStripePriceId,
            'features' => $validatedData['features'] ?? [],
            'is_active' => (bool) $validatedData['is_active'],
        ]);
    }
}
