<?php

declare(strict_types=1);

use App\Http\Controllers\Settings\Password\ShowPasswordController;
use App\Http\Controllers\Settings\Password\UpdatePasswordController;
use App\Http\Controllers\Settings\Profile\DeleteProfileController;
use App\Http\Controllers\Settings\Profile\ShowProfileController;
use App\Http\Controllers\Settings\Profile\UpdateProfileController;
use App\Http\Controllers\Settings\TwoFactorAuthentication\ShowTwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', ShowProfileController::class)->name('profile.edit');
    Route::patch('settings/profile', UpdateProfileController::class)->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', DeleteProfileController::class)->name('profile.destroy');

    Route::get('settings/password', ShowPasswordController::class)->name('user-password.edit');

    Route::put('settings/password', UpdatePasswordController::class)
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/appearance');
    })->name('appearance.edit');

    Route::get('settings/two-factor', ShowTwoFactorAuthenticationController::class)
        ->name('two-factor.show');
});
