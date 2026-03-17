<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Contracts\Stripe\StripePlanCatalog;
use App\Models\Plan;
use Illuminate\Support\Collection;

final readonly class SyncPlansFromStripeAction
{
    public function __construct(private StripePlanCatalog $stripePlanCatalog) {}

    /**
     * @return array{created: int, updated: int, deleted: int}
     */
    public function handle(): array
    {
        $stripePlans = collect($this->stripePlanCatalog->listProductsWithMonthlyPrices());
        $stripeProductIds = $stripePlans
            ->pluck('product_id')
            ->filter(fn (mixed $value): bool => is_string($value) && $value !== '')
            ->values();

        $deletedCount = $this->deleteMissingPlans($stripeProductIds);
        $createdCount = 0;
        $updatedCount = 0;

        /** @var Collection<int, string> $usedSlugs */
        $usedSlugs = Plan::query()->pluck('slug');

        $stripePlans->each(function (array $stripePlan) use (&$createdCount, &$updatedCount, $usedSlugs): void {
            $plan = Plan::query()->firstOrNew([
                'stripe_product_id' => (string) $stripePlan['product_id'],
            ]);

            $slug = $this->resolveUniqueSlug(
                preferredSlug: is_string($stripePlan['slug']) ? $stripePlan['slug'] : null,
                fallbackName: (string) $stripePlan['name'],
                productId: (string) $stripePlan['product_id'],
                usedSlugs: $usedSlugs,
                currentSlug: $plan->exists ? $plan->slug : null,
            );

            $plan->fill([
                'slug' => $slug,
                'name' => (string) $stripePlan['name'],
                'description' => $stripePlan['description'] !== null ? (string) $stripePlan['description'] : null,
                'price_monthly' => (int) $stripePlan['price_monthly'],
                'stripe_price_id' => $stripePlan['stripe_price_id'] !== null ? (string) $stripePlan['stripe_price_id'] : null,
                'is_active' => (bool) $stripePlan['is_active'],
                'features' => $plan->features ?? [],
            ]);

            $wasRecentlyCreated = ! $plan->exists;
            $plan->save();

            if ($wasRecentlyCreated) {
                $createdCount++;
            } else {
                $updatedCount++;
            }

            if (! $usedSlugs->contains($slug)) {
                $usedSlugs->push($slug);
            }
        });

        return [
            'created' => $createdCount,
            'updated' => $updatedCount,
            'deleted' => $deletedCount,
        ];
    }

    /**
     * @param  Collection<int, string>  $stripeProductIds
     */
    private function deleteMissingPlans(Collection $stripeProductIds): int
    {
        if ($stripeProductIds->isEmpty()) {
            return Plan::query()->delete();
        }

        return Plan::query()
            ->whereNull('stripe_product_id')
            ->orWhereNotIn('stripe_product_id', $stripeProductIds->all())
            ->delete();
    }

    /**
     * @param  Collection<int, string>  $usedSlugs
     */
    private function resolveUniqueSlug(
        ?string $preferredSlug,
        string $fallbackName,
        string $productId,
        Collection $usedSlugs,
        ?string $currentSlug,
    ): string {
        $baseSlug = mb_trim((string) $preferredSlug) !== ''
            ? str()->slug((string) $preferredSlug)
            : str()->slug($fallbackName);

        if ($baseSlug === '') {
            $baseSlug = 'plan-'.str()->lower(str()->substr($productId, -8));
        }

        $candidateSlug = $baseSlug;
        $suffix = 2;

        while ($usedSlugs->contains($candidateSlug) && $candidateSlug !== $currentSlug) {
            $candidateSlug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $candidateSlug;
    }
}
