<?php

declare(strict_types=1);

use App\Enums\Role as RoleEnum;
use App\Http\Controllers\Invitations\ShowAcceptInvitationController;
use App\Http\Controllers\Invitations\StoreAcceptInvitationController;
use App\Models\Invitation;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Notification;
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

    $this->seed(RolesAndPermissionsSeeder::class);

    Route::get('/invitations/{token}', ShowAcceptInvitationController::class)->name('invitations.accept');
    Route::post('/invitations/{token}', StoreAcceptInvitationController::class)->name('invitations.accept.store');
    Route::get('/dashboard', fn (): string => 'dashboard')->name('dashboard');
});

test('invitation accept page can be rendered for valid token', function (): void {
    $owner = User::factory()->create();
    $owner->assignRole(RoleEnum::Owner->value);

    $invitation = Invitation::query()->create([
        'email' => 'new.member@example.com',
        'role' => RoleEnum::Member->value,
        'token' => str_repeat('b', 64),
        'invited_by_id' => $owner->id,
        'expires_at' => now()->addDay(),
    ]);

    $this->get("/invitations/{$invitation->token}")
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('invitations/accept-invitation')
            ->where('invitation.email', 'new.member@example.com')
            ->where('token', $invitation->token));
});

test('invitation can be accepted', function (): void {
    Notification::fake();

    $owner = User::factory()->create();
    $owner->assignRole(RoleEnum::Owner->value);

    $invitation = Invitation::query()->create([
        'email' => 'new.member@example.com',
        'role' => RoleEnum::Member->value,
        'token' => str_repeat('c', 64),
        'invited_by_id' => $owner->id,
        'expires_at' => now()->addDay(),
    ]);

    $this->post("/invitations/{$invitation->token}", [
        'email' => 'new.member@example.com',
        'name' => 'New Member',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'new.member@example.com']);

    $user = User::query()->where('email', 'new.member@example.com')->firstOrFail();
    expect($user->hasRole(RoleEnum::Member->value))->toBeTrue();
    expect($invitation->fresh()?->accepted_at)->not->toBeNull();

    Notification::assertSentTo($user, VerifyEmailNotification::class);
});
