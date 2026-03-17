<?php

declare(strict_types=1);

namespace App\Services\Stripe;

use App\Contracts\Stripe\StripePlanCatalog;
use Stripe\Collection;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\Price;
use Stripe\Product;
use Stripe\StripeClient;
use Stripe\Subscription;

final readonly class StripeAdminService implements StripePlanCatalog
{
    public function __construct(private StripeClient $stripeClient) {}

    /**
     * @param  array<string, string>  $metadata
     *
     * @throws ApiErrorException
     */
    public function createProduct(string $name, ?string $description, array $metadata = []): Product
    {
        return $this->stripeClient->products->create([
            'name' => $name,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * @throws ApiErrorException
     */
    public function updateProduct(string $productId, string $name, ?string $description, bool $isActive): Product
    {
        return $this->stripeClient->products->update($productId, [
            'name' => $name,
            'description' => $description,
            'active' => $isActive,
        ]);
    }

    /**
     * @throws ApiErrorException
     */
    public function createMonthlyPrice(string $productId, int $amountInCents, string $currency = 'usd'): Price
    {
        return $this->stripeClient->prices->create([
            'product' => $productId,
            'unit_amount' => $amountInCents,
            'currency' => $currency,
            'recurring' => [
                'interval' => 'month',
            ],
        ]);
    }

    /**
     * @throws ApiErrorException
     */
    public function deactivatePrice(string $priceId): Price
    {
        return $this->stripeClient->prices->update($priceId, [
            'active' => false,
        ]);
    }

    /**
     * @throws ApiErrorException
     */
    public function retrieveSubscription(string $subscriptionId): Subscription
    {
        return $this->stripeClient->subscriptions->retrieve($subscriptionId, []);
    }

    /**
     * @throws ApiErrorException
     */
    public function listInvoices(int $limit = 50, ?string $status = null): Collection
    {
        return $this->stripeClient->invoices->all(array_filter([
            'limit' => $limit,
            'status' => $status,
        ]));
    }

    /**
     * @throws ApiErrorException
     */
    public function retrieveInvoice(string $invoiceId): Invoice
    {
        return $this->stripeClient->invoices->retrieve($invoiceId, []);
    }

    /**
     * @throws ApiErrorException
     */
    public function createRefund(string $chargeId, ?int $amountInCents = null, ?string $reason = null): \Stripe\Refund
    {
        return $this->stripeClient->refunds->create(array_filter([
            'charge' => $chargeId,
            'amount' => $amountInCents,
            'reason' => $reason,
        ]));
    }

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
     *
     * @throws ApiErrorException
     */
    public function listProductsWithMonthlyPrices(): array
    {
        $products = [];

        foreach ($this->listProducts() as $product) {
            $monthlyPrice = $this->resolveMonthlyPriceForProduct($product->id);

            $products[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'slug' => $product->metadata['slug'] ?? null,
                'is_active' => $product->active,
                'stripe_price_id' => $monthlyPrice?->id,
                'price_monthly' => $monthlyPrice?->unit_amount ?? 0,
            ];
        }

        return $products;
    }

    /**
     * @return list<Product>
     *
     * @throws ApiErrorException
     */
    private function listProducts(): array
    {
        $products = [];
        $startingAfter = null;

        do {
            $response = $this->stripeClient->products->all(array_filter([
                'limit' => 100,
                'starting_after' => $startingAfter,
            ]));

            foreach ($response->data as $product) {
                $products[] = $product;
            }

            $lastProduct = $response->data !== [] ? end($response->data) : null;
            $startingAfter = $response->has_more && $lastProduct instanceof Product ? $lastProduct->id : null;
        } while ($startingAfter !== null);

        return $products;
    }

    /**
     * @throws ApiErrorException
     */
    private function resolveMonthlyPriceForProduct(string $productId): ?Price
    {
        $prices = [];
        $startingAfter = null;

        do {
            $response = $this->stripeClient->prices->all(array_filter([
                'limit' => 100,
                'active' => true,
                'type' => 'recurring',
                'product' => $productId,
                'starting_after' => $startingAfter,
            ]));

            foreach ($response->data as $price) {
                if ($price->recurring?->interval === 'month') {
                    $prices[] = $price;
                }
            }

            $lastPrice = $response->data !== [] ? end($response->data) : null;
            $startingAfter = $response->has_more && $lastPrice instanceof Price ? $lastPrice->id : null;
        } while ($startingAfter !== null);

        if ($prices === []) {
            return null;
        }

        usort(
            $prices,
            fn (Price $left, Price $right): int => $right->created <=> $left->created,
        );

        return $prices[0];
    }
}
