<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Translation\TranslationLoader;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
