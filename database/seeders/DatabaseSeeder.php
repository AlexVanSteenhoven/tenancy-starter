<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = Permission::findOrCreate(PermissionEnum::AccessAdminPanel->value, 'web');
        $role = Role::findOrCreate('sysadmin', 'web');
        $role->givePermissionTo($permissions);

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
            ],
        );

        $adminUser->assignRole($role);

        // $this->call([
        //     PlanSeeder::class,
        // ]);
    }
}
