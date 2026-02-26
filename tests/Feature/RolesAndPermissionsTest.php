<?php

declare(strict_types=1);

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    if (! Schema::hasTable('users')) {
        $this->artisan('migrate', [
            '--path' => database_path('migrations/tenant/0001_01_01_000000_create_users_table.php'),
            '--realpath' => true,
        ])->assertSuccessful();

        $this->artisan('migrate', [
            '--path' => database_path('migrations/tenant/2025_08_14_170933_add_two_factor_columns_to_users_table.php'),
            '--realpath' => true,
        ])->assertSuccessful();
    }

    if (! Schema::hasTable('permissions')) {
        $this->artisan('migrate', [
            '--path' => database_path('migrations/tenant/2026_02_24_170219_create_permission_tables.php'),
            '--realpath' => true,
        ])->assertSuccessful();
    }
});

test('permission enum values use action resource format', function () {
    foreach (PermissionEnum::cases() as $permission) {
        expect($permission->value)->toMatch('/^[a-z]+:[a-z]+$/');
    }
});

test('permission mapping for roles is correct', function () {
    $ownerPermissions = array_map(
        static fn (PermissionEnum $permission): string => $permission->value,
        PermissionEnum::forRole(RoleEnum::Owner),
    );
    $adminPermissions = array_map(
        static fn (PermissionEnum $permission): string => $permission->value,
        PermissionEnum::forRole(RoleEnum::Admin),
    );
    $memberPermissions = array_map(
        static fn (PermissionEnum $permission): string => $permission->value,
        PermissionEnum::forRole(RoleEnum::Member),
    );

    sort($ownerPermissions);
    sort($adminPermissions);
    sort($memberPermissions);

    $allPermissionValues = array_map(
        static fn (PermissionEnum $permission): string => $permission->value,
        PermissionEnum::cases(),
    );
    sort($allPermissionValues);

    expect($ownerPermissions)->toBe($allPermissionValues);
    expect($adminPermissions)->toBe([
        PermissionEnum::InviteMembers->value,
        PermissionEnum::ManageRoles->value,
        PermissionEnum::RemoveMembers->value,
        PermissionEnum::ViewMembers->value,
    ]);
    expect($memberPermissions)->toBe([
        PermissionEnum::ViewMembers->value,
    ]);
});

test('roles and permissions seeder creates expected records', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $permissionNames = Permission::query()->pluck('name')->sort()->values()->all();
    $roleNames = Role::query()->pluck('name')->sort()->values()->all();

    expect($permissionNames)->toBe([
        PermissionEnum::InviteMembers->value,
        PermissionEnum::ManageRoles->value,
        PermissionEnum::ManageWorkspace->value,
        PermissionEnum::RemoveMembers->value,
        PermissionEnum::ViewMembers->value,
    ]);
    expect($roleNames)->toBe([
        RoleEnum::Admin->value,
        RoleEnum::Member->value,
        RoleEnum::Owner->value,
    ]);
});

test('users inherit permissions from assigned role', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole(RoleEnum::Admin->value);

    expect($user->hasRole(RoleEnum::Admin->value))->toBeTrue();
    expect($user->hasPermissionTo(PermissionEnum::ManageRoles->value))->toBeTrue();
    expect($user->hasPermissionTo(PermissionEnum::ManageWorkspace->value))->toBeFalse();
});
