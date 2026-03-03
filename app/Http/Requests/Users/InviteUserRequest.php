<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Enums\Permission;
use App\Enums\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class InviteUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo(
            permission: Permission::InviteUsers
        ) ?? false;
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
                    ->where(fn ($query) => $query->where('expires_at', '>', now())),
            ],
            'role' => [
                'required',
                Rule::enum(Role::class),
                Rule::notIn([Role::Owner->value]),
            ],
        ];
    }
}
