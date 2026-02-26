<?php

declare(strict_types=1);

use App\Http\Controllers\Users\ShowUsersController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();

    if (! Schema::hasTable('users')) {
        Schema::create('users', function (Blueprint $table): void {
            $table->uuid('id')->primary();
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

    Route::get('/users', ShowUsersController::class)->name('users.index');
});

test('users page can be rendered', function (): void {
    User::factory()->count(3)->create();

    $response = $this->get('/users');

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('users/show-users')
            ->has('users', 3));
});
