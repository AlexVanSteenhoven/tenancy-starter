<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\Workspace;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class SyncTenantRolesAndPermissions extends Command
{
    protected $signature = 'sync:authorization';

    protected $description = 'Sync enum roles and permissions to all tenant databases';

    public function handle(): int
    {
        $guardName = (string) config('auth.defaults.guard', 'web');
        /** @var \Illuminate\Database\Eloquent\Collection<int, Workspace> $tenants */
        $tenants = Workspace::query()->get();

        if ($tenants->isEmpty()) {
            $this->warn('No tenants found to sync.');

            return self::SUCCESS;
        }

        $this->info("Syncing roles and permissions for {$tenants->count()} tenant(s)...");

        foreach ($tenants as $workspace) {
            $this->line("-> Tenant {$workspace->id}");

            tenancy()->initialize($workspace);

            try {
                $result = $this->syncTenantRolesAndPermissions($guardName);
            } finally {
                tenancy()->end();
            }

            $this->line(
                sprintf(
                    '   permissions: <fg=green;options=bold>+%d</> new, <fg=yellow;options=bold>%d</> existing; roles: <fg=green;options=bold>+%d</> new, <fg=yellow;options=bold>%d</> existing; links: <fg=green;options=bold>+%d</> new, <fg=yellow;options=bold>%d</> existing',
                    $result['permissions_created'],
                    $result['permissions_existing'],
                    $result['roles_created'],
                    $result['roles_existing'],
                    $result['role_permissions_created'],
                    $result['role_permissions_existing'],
                ),
            );
        }

        $this->info('Sync complete.');

        return self::SUCCESS;
    }

    /**
     * @return array{
     *     permissions_created: int,
     *     permissions_existing: int,
     *     roles_created: int,
     *     roles_existing: int,
     *     role_permissions_created: int,
     *     role_permissions_existing: int
     * }
     */
    private function syncTenantRolesAndPermissions(string $guardName): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionsCreated = 0;
        $permissionsExisting = 0;
        $rolesCreated = 0;
        $rolesExisting = 0;
        $rolePermissionsCreated = 0;
        $rolePermissionsExisting = 0;

        foreach (PermissionEnum::cases() as $permissionEnum) {
            $permission = Permission::query()->firstOrCreate([
                'name' => $permissionEnum->value,
                'guard_name' => $guardName,
            ]);

            if ($permission->wasRecentlyCreated) {
                $permissionsCreated++;
            } else {
                $permissionsExisting++;
            }
        }

        foreach (RoleEnum::cases() as $roleEnum) {
            $role = Role::query()->firstOrCreate([
                'name' => $roleEnum->value,
                'guard_name' => $guardName,
            ]);

            if ($role->wasRecentlyCreated) {
                $rolesCreated++;
            } else {
                $rolesExisting++;
            }

            $targetPermissionNames = array_map(
                static fn (PermissionEnum $permission): string => $permission->value,
                PermissionEnum::forRole($roleEnum),
            );
            $existingPermissionNames = $role->permissions()->pluck('name')->all();
            $missingPermissionNames = array_values(array_diff($targetPermissionNames, $existingPermissionNames));

            if ($missingPermissionNames !== []) {
                $role->givePermissionTo($missingPermissionNames);
            }

            $rolePermissionsCreated += count($missingPermissionNames);
            $rolePermissionsExisting += count($targetPermissionNames) - count($missingPermissionNames);
        }

        return [
            'permissions_created' => $permissionsCreated,
            'permissions_existing' => $permissionsExisting,
            'roles_created' => $rolesCreated,
            'roles_existing' => $rolesExisting,
            'role_permissions_created' => $rolePermissionsCreated,
            'role_permissions_existing' => $rolePermissionsExisting,
        ];
    }
}
