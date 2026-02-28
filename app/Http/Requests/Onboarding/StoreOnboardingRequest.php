<?php

declare(strict_types=1);

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use Stancl\Tenancy\Database\Models\Domain;

final class StoreOnboardingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'workspace' => ['required', 'unique:workspaces,name', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $subdomain = Str::slug((string) $this->input('workspace'));

            if ($subdomain === '') {
                $validator->errors()->add('workspace', __('onboarding.validation.invalid_subdomain'));

                return;
            }

            if (Domain::query()->where('domain', $subdomain)->exists()) {
                $validator->errors()->add('workspace', __('onboarding.validation.subdomain_taken'));
            }
        });
    }
}
