<?php

declare(strict_types=1);

namespace App\Http\Requests\Onboarding;

use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreBillingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->has('onboarding_workspace_id');
    }

    public function rules(): array
    {
        $activePlansRule = Rule::exists('plans', 'slug')
            ->where(fn ($query) => $query->where('is_active', true));

        return [
            'plan' => ['required', 'string', $activePlansRule],
            'seats' => ['required', 'integer', 'min:1'],
            'payment_method' => [
                Rule::requiredIf(function (): bool {
                    $selectedPlan = Plan::query()
                        ->where('slug', (string) $this->input('plan'))
                        ->where('is_active', true)
                        ->first();

                    return $selectedPlan instanceof Plan && $selectedPlan->price_monthly > 0;
                }),
                'string',
                'max:255',
            ],
        ];
    }
}
