<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Plan $plan */
        $plan = $this->route('plan');

        return [
            'slug' => ['required', 'alpha_dash', 'max:120', Rule::unique('plans', 'slug')->ignore($plan->id)],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'price_monthly' => ['required', 'integer', 'min:0'],
            'features' => ['nullable', 'array'],
            'features.*' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
