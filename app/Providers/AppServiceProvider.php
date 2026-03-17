<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Onboarding\SubscribeWorkspaceToPlanAction;
use App\Contracts\Onboarding\SubscribeWorkspaceToPlan;
use App\Contracts\Stripe\StripePlanCatalog;
use App\Models\Workspace;
use App\Services\Stripe\StripeAdminService;
use App\Services\Translation\TranslationLoader;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Stripe\StripeClient;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SubscribeWorkspaceToPlan::class, SubscribeWorkspaceToPlanAction::class);
        $this->app->singleton(StripeClient::class, fn (): StripeClient => new StripeClient((string) config('cashier.secret')));
        $this->app->bind(StripePlanCatalog::class, StripeAdminService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Cashier::useCustomerModel(Workspace::class);
        $this->configureTranslations();
    }

    private function configureTranslations(): void
    {
        $this->app->extend(
            abstract: 'translation.loader',
            closure: fn ($_, $app): TranslationLoader => new TranslationLoader(
                files: $app['files'],
                path: $app['path.lang'],
            )
        );
    }
}
