<?php

declare(strict_types=1);

use App\Enums\Permission;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\LogoutController;
use App\Http\Controllers\Admin\Auth\ShowLoginController;
use App\Http\Controllers\Admin\Invoices\ShowInvoiceController;
use App\Http\Controllers\Admin\Invoices\ShowInvoicesController;
use App\Http\Controllers\Admin\Invoices\StoreRefundController;
use App\Http\Controllers\Admin\Plans\DeactivatePlanController;
use App\Http\Controllers\Admin\Plans\ShowPlansController;
use App\Http\Controllers\Admin\Plans\StorePlanController;
use App\Http\Controllers\Admin\Plans\SyncPlansFromStripeController;
use App\Http\Controllers\Admin\Plans\UpdatePlanController;
use App\Http\Controllers\Admin\ShowDashboardController;
use App\Http\Controllers\Admin\Subscriptions\ShowSubscriptionController;
use App\Http\Controllers\Admin\Subscriptions\ShowSubscriptionsController;
use App\Http\Controllers\Admin\Workspaces\CancelWorkspaceSubscriptionController;
use App\Http\Controllers\Admin\Workspaces\ShowWorkspaceController;
use App\Http\Controllers\Admin\Workspaces\ShowWorkspacesController;
use App\Http\Controllers\Admin\Workspaces\UpdateWorkspaceSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::prefix('_')->as('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', ShowLoginController::class)->name('login');
        Route::post('login', LoginController::class)->name('login.store');
    });

    Route::middleware(['auth', 'permission:'.Permission::AccessAdminPanel->value])->group(function () {
        Route::get('/', ShowDashboardController::class)->name('dashboard');
        Route::post('logout', LogoutController::class)->name('logout');

        Route::prefix('workspaces')->as('workspaces.')->group(function () {
            Route::get('/', ShowWorkspacesController::class)->name('index');
            Route::get('{workspace}', ShowWorkspaceController::class)->name('show');
            Route::patch('{workspace}/subscription', UpdateWorkspaceSubscriptionController::class)->name('subscription.update');
            Route::delete('{workspace}/subscription', CancelWorkspaceSubscriptionController::class)->name('subscription.cancel');
        });

        Route::prefix('plans')->as('plans.')->group(function () {
            Route::get('/', ShowPlansController::class)->name('index');
            Route::post('sync', SyncPlansFromStripeController::class)->name('sync');
            Route::post('/', StorePlanController::class)->name('store');
            Route::patch('{plan}', UpdatePlanController::class)->name('update');
            Route::delete('{plan}', DeactivatePlanController::class)->name('deactivate');
        });

        Route::prefix('subscriptions')->as('subscriptions.')->group(function () {
            Route::get('/', ShowSubscriptionsController::class)->name('index');
            Route::get('{subscription}', ShowSubscriptionController::class)->name('show');
        });

        Route::prefix('invoices')->as('invoices.')->group(function () {
            Route::get('/', ShowInvoicesController::class)->name('index');
            Route::get('{invoice}', ShowInvoiceController::class)->name('show');
            Route::post('{invoice}/refunds', StoreRefundController::class)->name('refunds.store');
        });
    });
});

require __DIR__.'/settings.php';
