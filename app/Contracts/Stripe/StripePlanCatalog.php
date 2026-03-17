<?php

declare(strict_types=1);

namespace App\Contracts\Stripe;

interface StripePlanCatalog
{
    /**
     * @return list<array{
     *     product_id: string,
     *     name: string,
     *     description: string|null,
     *     slug: string|null,
     *     is_active: bool,
     *     stripe_price_id: string|null,
     *     price_monthly: int
     * }>
     */
    public function listProductsWithMonthlyPrices(): array;
}
