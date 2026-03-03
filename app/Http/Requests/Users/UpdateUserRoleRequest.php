<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Enums\Permission;
use App\Enums\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo(
            permission: Permission::ManageMembers
        ) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => [
                'required',
                Rule::enum(Role::class),
                Rule::notIn([Role::Owner->value]),
            ],
        ];
    }
}
