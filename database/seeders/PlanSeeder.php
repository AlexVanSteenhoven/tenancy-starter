<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Plan as WorkspacePlan;
use App\Models\Plan;
use Illuminate\Database\Seeder;

final class PlanSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            [
                'slug' => WorkspacePlan::Free->value,
                'name' => 'Free',
                'description' => 'Starter plan',
                'price_monthly' => 0,
                'stripe_price_id' => null,
                'features' => [
                    'onboarding.billing.plans.free.features.0',
                    'onboarding.billing.plans.free.features.1',
                    'onboarding.billing.plans.free.features.2',
                ],
                'is_active' => true,
            ],
            [
                'slug' => WorkspacePlan::Pro->value,
                'name' => 'Pro',
                'description' => 'Professional plan',
                'price_monthly' => 2900,
                'stripe_price_id' => env('STRIPE_PRICE_PRO'),
                'features' => [
                    'onboarding.billing.plans.pro.features.0',
                    'onboarding.billing.plans.pro.features.1',
                    'onboarding.billing.plans.pro.features.2',
                ],
                'is_active' => true,
            ],
            [
                'slug' => WorkspacePlan::Business->value,
                'name' => 'Business',
                'description' => 'Business plan',
                'price_monthly' => 9900,
                'stripe_price_id' => env('STRIPE_PRICE_BUSINESS'),
                'features' => [
                    'onboarding.billing.plans.business.features.0',
                    'onboarding.billing.plans.business.features.1',
                    'onboarding.billing.plans.business.features.2',
                ],
                'is_active' => true,
            ],
        ] as $plan) {
            Plan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                $plan,
            );
        }
    }
}
