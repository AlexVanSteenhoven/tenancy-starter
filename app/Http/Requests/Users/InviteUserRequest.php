<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Enums\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class InviteUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        return $user->hasAnyRole([Role::Owner->value, Role::Admin->value]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:255',
                'lowercase',
                Rule::unique('users', 'email'),
                Rule::unique('invitations', 'email')
                    ->whereNull('accepted_at')
                    ->where('expires_at', '>', now()),
            ],
            'role' => [
                'required',
                Rule::enum(Role::class),
                Rule::notIn([Role::Owner->value]),
            ],
        ];
    }
}
