<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guardName = (string) config('auth.defaults.guard', 'web');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionEnum::cases() as $permission) {
            \Spatie\Permission\Models\Permission::findOrCreate($permission->value, $guardName);
        }

        foreach (RoleEnum::cases() as $role) {
            $roleModel = Role::findOrCreate($role->value, $guardName);

            $permissionNames = array_map(
                static fn (PermissionEnum $permission): string => $permission->value,
                PermissionEnum::forRole($role),
            );

            $roleModel->syncPermissions($permissionNames);
        }
    }
}
