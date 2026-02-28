<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Enums\Role;
use App\Http\Requests\Users\UpdateUserRoleRequest;
use App\Models\User;
use Spatie\Permission\Models\Role as RoleModel;

final readonly class UpdateUserRoleAction
{
    public function handle(UpdateUserRoleRequest $request, User $user): void
    {
        if ($user->hasRole(Role::Owner->value)) {
            return;
        }

        $role = (string) $request->input('role');
        $guardName = (string) config('auth.defaults.guard', 'web');

        RoleModel::findOrCreate($role, $guardName);

        $user->syncRoles([$role]);
    }
}
