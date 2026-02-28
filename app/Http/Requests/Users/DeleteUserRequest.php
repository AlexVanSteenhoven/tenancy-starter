<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class DeleteUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();
        $target = $this->targetUser();

        if (! $actor instanceof User || ! $target instanceof User) {
            return false;
        }

        if ((string) $actor->id === (string) $target->id) {
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
        return [];
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
