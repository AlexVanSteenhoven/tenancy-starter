<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();
        $target = $this->targetUser();

        if (! $actor instanceof User || ! $target instanceof User) {
            return false;
        }

        if (! $actor->hasAnyRole([Role::Owner->value, Role::Admin->value])) {
            return false;
        }

        if ($target->hasRole(Role::Owner->value) && ! $actor->hasRole(Role::Owner->value)) {
            return false;
        }

        return true;
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $target = $this->targetUser();

            if (! $target instanceof User) {
                return;
            }

            if ($target->hasRole(Role::Owner->value)) {
                $validator->errors()->add('role', __('users.validation.owner_role_locked'));
            }
        });
    }

    private function targetUser(): ?User
    {
        $routeUser = $this->route('user');

        if ($routeUser instanceof User) {
            return $routeUser;
        }

        if (! is_string($routeUser) || $routeUser === '') {
            return null;
        }

        return User::query()->find($routeUser);
    }
}
