<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Enums\Status;
use App\Http\Controllers\Users\ShowUsersController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();

    if (! Schema::hasTable('users')) {
        $this->artisan('migrate', [
            '--path' => database_path('migrations/tenant/0001_01_01_000000_create_users_table.php'),
            '--realpath' => true,
        ])->assertSuccessful();

        $this->artisan('migrate', [
            '--path' => database_path('migrations/tenant/2025_08_14_170933_add_two_factor_columns_to_users_table.php'),
            '--realpath' => true,
        ])->assertSuccessful();

        $this->artisan('migrate', [
            '--path' => database_path('migrations/tenant/2026_02_26_145649_add_status_to_users_table.php'),
            '--realpath' => true,
        ])->assertSuccessful();
    }

    if (! Schema::hasTable('roles')) {
        $this->artisan('migrate', [
            '--path' => database_path('migrations/tenant/2026_02_24_170219_create_permission_tables.php'),
            '--realpath' => true,
        ])->assertSuccessful();
    }

    if (! Schema::hasTable('invitations')) {
        $this->artisan('migrate', [
            '--path' => database_path('migrations/tenant/2026_02_28_011038_create_invitations_table.php'),
            '--realpath' => true,
        ])->assertSuccessful();
    }

    Route::get('/users', ShowUsersController::class)->name('users.index');
});

test('users page can be rendered', function (): void {
    User::factory()->count(3)->create();

    $response = $this->get('/users');

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('users/show-users')
            ->has('users', 3)
            ->has('users.0.status')
            ->has('permissions')
            ->has('permissions.canInviteUsers')
            ->has('permissions.canUpdateUsers')
            ->has('permissions.canDeleteUsers')
            ->has('permissions.canViewUsers')
            ->has('filters.status', count(Status::cases()))
            ->has('filters.role', count(Role::cases()))
            ->where('filters.status.0', Status::Active->value)
            ->where('filters.role.0', Role::Owner->value)
            ->where('permissions.canInviteUsers', false)
            ->where('permissions.canUpdateUsers', false)
            ->where('permissions.canDeleteUsers', false)
            ->where('permissions.canViewUsers', false));
});
