<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Enums\Role;
use App\Enums\Status;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateUserStatusRequest extends FormRequest
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
            'status' => [
                'required',
                Rule::enum(Status::class),
                Rule::notIn([Status::Deleted->value, Status::Archived->value]),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $actor = $this->user();
            $target = $this->targetUser();

            if (! $actor instanceof User || ! $target instanceof User) {
                return;
            }

            if ((string) $actor->id === (string) $target->id) {
                $validator->errors()->add('status', __('users.validation.cannot_update_self_status'));
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
