<?php

declare(strict_types=1);

use App\Http\Controllers\Invitations\ShowAcceptInvitationController;
use App\Http\Controllers\Invitations\StoreAcceptInvitationController;
use App\Http\Controllers\Onboarding\ShowSetupAccountController;
use App\Http\Controllers\Onboarding\StoreSetupAccountController;
use App\Http\Controllers\Settings\Password\ShowPasswordController;
use App\Http\Controllers\Settings\Password\UpdatePasswordController;
use App\Http\Controllers\Settings\Profile\DeleteProfileController;
use App\Http\Controllers\Settings\Profile\ShowProfileController;
use App\Http\Controllers\Settings\Profile\UpdateProfileController;
use App\Http\Controllers\Settings\ShowAppearanceController;
use App\Http\Controllers\Settings\TwoFactorAuthentication\ShowTwoFactorAuthenticationController;
use App\Http\Controllers\Users\DeleteUserController;
use App\Http\Controllers\Users\InviteUserController;
use App\Http\Controllers\Users\ShowUsersController;
use App\Http\Controllers\Users\UpdateUserRoleController;
use App\Http\Controllers\Users\UpdateUserStatusController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Stancl\Tenancy\Middleware\ScopeSessions;

Route::middleware([InitializeTenancyBySubdomain::class, PreventAccessFromCentralDomains::class, 'web', ScopeSessions::class])
    ->group(function () {
        // Public routes (Set up account)
        Route::prefix('onboarding')->as('onboarding.')->group(function () {
            Route::get('account/create', ShowSetupAccountController::class)->name('account.create');
            Route::post('account', StoreSetupAccountController::class)->name('account.create.store');
        });

        Route::prefix('invitations')->as('invitations.')->group(function () {
            Route::get('{token}', ShowAcceptInvitationController::class)->name('accept');
            Route::post('{token}', StoreAcceptInvitationController::class)->name('accept.store');
        });

        Route::middleware(['auth', 'verified'])->group(function () {
            Route::get('/dashboard', function () {
                return Inertia::render('dashboard');
            })->name('dashboard');

            Route::prefix('users')->as('users.')->group(function () {
                Route::get('/', ShowUsersController::class)->name('index');
                Route::post('invite', InviteUserController::class)->name('invite');
                Route::patch('{user}/role', UpdateUserRoleController::class)->name('role.update');
                Route::patch('{user}/status', UpdateUserStatusController::class)->name('status.update');
                Route::delete('{user}', DeleteUserController::class)->name('delete');
            });
        });

        // Settings routes
        Route::prefix('settings')
            ->middleware('auth')
            ->as('settings.')->group(function () {
                Route::redirect('/', '/settings/profile');

                // Profile
                Route::get('profile', ShowProfileController::class)->name('profile.edit');
                Route::patch('profile', UpdateProfileController::class)->name('profile.update');

                Route::middleware('verified')->group(function () {
                    Route::delete('profile', DeleteProfileController::class)->name('profile.destroy');

                    // Password
                    Route::get('password', ShowPasswordController::class)->name('password.edit');
                    Route::put('password', UpdatePasswordController::class)
                        ->middleware('throttle:6,1')
                        ->name('password.update');

                    // Appearance
                    Route::get('appearance', ShowAppearanceController::class)->name('appearance.edit');

                    // Two-Factor Authentication
                    Route::get('two-factor', ShowTwoFactorAuthenticationController::class)->name('two-factor.show');
                });
            });
    });
