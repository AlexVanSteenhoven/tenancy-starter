<?php

declare(strict_types=1);

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Enums\Status;
use App\Http\Controllers\Users\DeletePendingInvitationController;
use App\Http\Controllers\Users\DeleteUserController;
use App\Http\Controllers\Users\InviteUserController;
use App\Http\Controllers\Users\ResendPendingInvitationController;
use App\Http\Controllers\Users\UpdateUserRoleController;
use App\Http\Controllers\Users\UpdateUserStatusController;
use App\Models\Invitation;
use App\Models\User;
use App\Notifications\InviteUserNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
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

    Route::bind('user', static fn (string $value): User => User::query()->findOrFail($value));

    Route::post('/users/invite', InviteUserController::class)->name('users.invite');
    Route::post('/users/invitations/{invitation}/resend', ResendPendingInvitationController::class)->name('users.invitations.resend');
    Route::patch('/users/{user}/role', UpdateUserRoleController::class)->name('users.role.update');
    Route::patch('/users/{user}/status', UpdateUserStatusController::class)->name('users.status.update');
    Route::delete('/users/{user}', DeleteUserController::class)->name('users.delete');
    Route::delete('/users/invitations/{invitation}', DeletePendingInvitationController::class)->name('users.invitations.delete');
    Route::get('/invitations/{token}', fn (): string => 'ok')->name('invitations.accept');
});

test('owner can invite a user', function (): void {
    Notification::fake();

    $owner = User::factory()->create();
    $owner->assignRole(RoleEnum::Owner->value);

    $this->actingAs($owner)
        ->post('/users/invite', [
            'email' => 'invited@example.com',
            'role' => RoleEnum::Member->value,
        ])
        ->assertRedirect()
        ->assertSessionHas('status', __('notifications.users.invite.title'))
        ->assertSessionHas('statusDescription', __('notifications.users.invite.description'));

    $this->assertDatabaseHas('invitations', [
        'email' => 'invited@example.com',
        'role' => RoleEnum::Member->value,
        'invited_by_id' => $owner->id,
    ]);

    Notification::assertSentOnDemand(InviteUserNotification::class);
});

test('owner can update user role', function (): void {
    $owner = User::factory()->create();
    $owner->assignRole(RoleEnum::Owner->value);

    $member = User::factory()->create();
    $member->assignRole(RoleEnum::Member->value);

    $this->actingAs($owner)
        ->patch("/users/{$member->id}/role", [
            'role' => RoleEnum::Admin->value,
        ])
        ->assertRedirect()
        ->assertSessionHas('status', __('notifications.users.role-update.title'))
        ->assertSessionHas('statusDescription', __('notifications.users.role-update.description'));

    expect($member->fresh()?->hasRole(RoleEnum::Admin->value))->toBeTrue();
});

test('owner can update user status', function (): void {
    $owner = User::factory()->create();
    $owner->assignRole(RoleEnum::Owner->value);

    $member = User::factory()->create([
        'status' => Status::Active->value,
    ]);
    $member->assignRole(RoleEnum::Member->value);

    $this->actingAs($owner)
        ->patch("/users/{$member->id}/status", [
            'status' => Status::Inactive->value,
        ])
        ->assertRedirect()
        ->assertSessionHas('status', __('notifications.users.status-update.title'))
        ->assertSessionHas('statusDescription', __('notifications.users.status-update.description'));

    expect($member->fresh()?->status)->toBe(Status::Inactive->value);
});

test('user with update users permission can update user role', function (): void {
    $actor = User::factory()->create();
    $actor->assignRole(RoleEnum::Member->value);
    $actor->givePermissionTo(PermissionEnum::UpdateUsers->value);

    $target = User::factory()->create();
    $target->assignRole(RoleEnum::Member->value);

    $this->actingAs($actor)
        ->patch("/users/{$target->id}/role", [
            'role' => RoleEnum::Admin->value,
        ])
        ->assertRedirect();

    expect($target->fresh()?->hasRole(RoleEnum::Admin->value))->toBeTrue();
});

test('user with update users permission can update owner status', function (): void {
    $actor = User::factory()->create();
    $actor->assignRole(RoleEnum::Member->value);
    $actor->givePermissionTo(PermissionEnum::UpdateUsers->value);

    $owner = User::factory()->create([
        'status' => Status::Active->value,
    ]);
    $owner->assignRole(RoleEnum::Owner->value);

    $this->actingAs($actor)
        ->patch("/users/{$owner->id}/status", [
            'status' => Status::Inactive->value,
        ])
        ->assertRedirect();

    expect($owner->fresh()?->status)->toBe(Status::Inactive->value);
});

test('owner can delete a user', function (): void {
    $owner = User::factory()->create();
    $owner->assignRole(RoleEnum::Owner->value);

    $member = User::factory()->create();
    $member->assignRole(RoleEnum::Member->value);

    Invitation::query()->create([
        'email' => $member->email,
        'role' => RoleEnum::Member->value,
        'token' => str_repeat('a', 64),
        'invited_by_id' => $owner->id,
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($owner)
        ->delete("/users/{$member->id}")
        ->assertRedirect()
        ->assertSessionHas('status', __('notifications.users.delete.title'))
        ->assertSessionHas('statusDescription', __('notifications.users.delete.description'));

    $this->assertDatabaseMissing('users', ['id' => $member->id]);
    $this->assertDatabaseMissing('invitations', ['email' => $member->email]);
});

test('owner can delete a pending invitation', function (): void {
    $owner = User::factory()->create();
    $owner->assignRole(RoleEnum::Owner->value);

    $invitation = Invitation::query()->create([
        'email' => 'pending@example.com',
        'role' => RoleEnum::Member->value,
        'token' => str_repeat('b', 64),
        'invited_by_id' => $owner->id,
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($owner)
        ->delete("/users/invitations/{$invitation->id}")
        ->assertRedirect()
        ->assertSessionHas('status', __('notifications.users.invitation-delete.title'))
        ->assertSessionHas('statusDescription', __('notifications.users.invitation-delete.description'));

    $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);
});

test('owner can resend a pending invitation email', function (): void {
    Notification::fake();

    $owner = User::factory()->create();
    $owner->assignRole(RoleEnum::Owner->value);

    $invitation = Invitation::query()->create([
        'email' => 'resend@example.com',
        'role' => RoleEnum::Member->value,
        'token' => str_repeat('c', 64),
        'invited_by_id' => $owner->id,
        'expires_at' => now()->addDay(),
    ]);

    $previousToken = $invitation->token;

    $this->actingAs($owner)
        ->post("/users/invitations/{$invitation->id}/resend")
        ->assertRedirect()
        ->assertSessionHas('status', __('notifications.users.resend.title'))
        ->assertSessionHas('statusDescription', __('notifications.users.resend.description'));

    expect($invitation->fresh()?->token)->not->toBe($previousToken);

    Notification::assertSentOnDemand(InviteUserNotification::class);
});
