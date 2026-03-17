<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Http\Requests\Admin\StorePlanRequest;
use App\Models\Plan;
use App\Services\Stripe\StripeAdminService;

final readonly class StorePlanAction
{
    public function __construct(private StripeAdminService $stripeAdminService) {}

    public function handle(StorePlanRequest $request): Plan
    {
        $validatedData = $request->validated();

        $product = $this->stripeAdminService->createProduct(
            name: (string) $validatedData['name'],
            description: $validatedData['description'] ?? null,
            metadata: ['slug' => (string) $validatedData['slug']],
        );

        $stripePriceId = null;
        if ((int) $validatedData['price_monthly'] > 0) {
            $price = $this->stripeAdminService->createMonthlyPrice(
                productId: $product->id,
                amountInCents: (int) $validatedData['price_monthly'],
            );

            $stripePriceId = $price->id;
        }

        return Plan::create([
            'slug' => $validatedData['slug'],
            'name' => $validatedData['name'],
            'description' => $validatedData['description'] ?? null,
            'price_monthly' => (int) $validatedData['price_monthly'],
            'stripe_product_id' => $product->id,
            'stripe_price_id' => $stripePriceId,
            'features' => $validatedData['features'] ?? [],
            'is_active' => (bool) ($validatedData['is_active'] ?? true),
        ]);
    }
}
