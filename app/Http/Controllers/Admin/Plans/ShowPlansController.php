<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Plans;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Inertia\Inertia;
use Inertia\Response;

final class ShowPlansController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('admin/plans/index', [
            'plans' => Plan::query()
                ->orderBy('price_monthly')
                ->get()
                ->map(fn (Plan $plan): array => [
                    'id' => $plan->id,
                    'slug' => $plan->slug,
                    'name' => $plan->name,
                    'description' => $plan->description,
                    'price_monthly' => $plan->price_monthly,
                    'stripe_product_id' => $plan->stripe_product_id,
                    'stripe_price_id' => $plan->stripe_price_id,
                    'features' => $plan->features ?? [],
                    'is_active' => $plan->is_active,
                ]),
        ]);
    }
}
