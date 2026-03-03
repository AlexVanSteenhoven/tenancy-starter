<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Http\Controllers\Users\ShowUserController;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
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

    $this->seed(RolesAndPermissionsSeeder::class);

    Route::get('/users/{user}', ShowUserController::class)->name('users.show');
});

test('user page can be rendered', function (): void {
    $user = User::factory()->create();
    $user->assignRole(Role::Member->value);

    $response = $this->get("/users/{$user->id}");

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('users/show-user')
            ->where('user.id', $user->id)
            ->where('user.email', $user->email)
            ->where('user.role', Role::Member->value)
            ->has('user.status'));
});
