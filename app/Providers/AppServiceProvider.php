<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Translation\TranslationLoader;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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
