<?php

declare(strict_types=1);

use App\Http\Controllers\Onboarding\ShowBillingController;
use App\Http\Controllers\Onboarding\ShowOnboardingController;
use App\Http\Controllers\Onboarding\StoreBillingController;
use App\Http\Controllers\Onboarding\StoreOnboardingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

// Onboarding routes
Route::prefix('onboarding')->as('onboarding.')->group(function () {
    Route::get('create-workspace', ShowOnboardingController::class)->name('create-workspace');
    Route::post('create-workspace', StoreOnboardingController::class)->name('create-workspace.store');
    Route::get('billing', ShowBillingController::class)->name('billing');
    Route::post('billing', StoreBillingController::class)->name('billing.store');
})->middleware(['web', 'guest']);

Route::post('/stripe/webhook', [Laravel\Cashier\Http\Controllers\WebhookController::class, 'handleWebhook']);
