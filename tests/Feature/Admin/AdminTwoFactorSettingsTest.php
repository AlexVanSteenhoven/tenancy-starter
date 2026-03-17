<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function (): void {
    $this->withoutVite();
});

test('verified user can access two factor settings on admin domain', function (): void {
    $user = User::create([
        'name' => 'Admin User',
        'email' => 'admin-'.fake()->uuid().'@example.com',
        'password' => 'password',
    ]);
    $user->forceFill([
        'email_verified_at' => now(),
    ])->save();

    $response = $this
        ->actingAs($user)
        ->get('http://admin.tenancy-starter.test/settings/two-factor');

    $response->assertSuccessful();
});
