<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Workspace;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function (): void {
    $this->withoutVite();
    Event::fake([TenantCreated::class]);

    config()->set(
        'tenancy.bootstrappers',
        array_values(array_filter(
            config('tenancy.bootstrappers'),
            fn (string $bootstrapper): bool => $bootstrapper !== DatabaseTenancyBootstrapper::class,
        )),
    );

    if (! Schema::hasTable('users')) {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }
});

test('setup account screen can be rendered on tenant domain', function (): void {
    createSetupAccountWorkspace('Acme Workspace', 'acme');

    $response = $this->get('http://acme.tenancy-starter.test/onboarding/account/create?email=owner@acme.com');

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('auth/setup-account')
            ->where('email', 'owner@acme.com'));
});

test('setup account screen redirects to login when tenant already has users', function (): void {
    createSetupAccountWorkspace('Acme Workspace', 'acme');

    User::query()->create([
        'name' => 'Existing User',
        'email' => 'existing@acme.com',
        'password' => 'password',
    ]);

    $response = $this->get('http://acme.tenancy-starter.test/onboarding/account/create?email=owner@acme.com');

    $response->assertRedirect('/login');
});

test('setup account can create first tenant user and redirect to dashboard', function (): void {
    createSetupAccountWorkspace('Acme Workspace', 'acme');
    Notification::fake();

    $response = $this->post('http://acme.tenancy-starter.test/onboarding/account', [
        'name' => 'Owner User',
        'email' => 'owner@acme.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertDatabaseHas('users', ['email' => 'owner@acme.com']);
    $this->assertAuthenticated();

    $user = User::query()->where('email', 'owner@acme.com')->firstOrFail();
    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

test('setup account request validates required fields', function (): void {
    createSetupAccountWorkspace('Acme Workspace', 'acme');

    $response = $this->post('http://acme.tenancy-starter.test/onboarding/account', []);

    $response->assertSessionHasErrors(['name', 'email', 'password']);
});

function createSetupAccountWorkspace(string $workspaceName, string $subdomain): Workspace
{
    $workspace = Workspace::query()->create([
        'name' => $workspaceName,
    ]);

    $workspace->domains()->create([
        'domain' => $subdomain.'.tenancy-starter.test',
    ]);

    return $workspace;
}
