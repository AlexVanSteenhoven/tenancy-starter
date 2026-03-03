<?php

declare(strict_types=1);

use App\Models\Workspace;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper;
use Stancl\Tenancy\Events\TenantCreated;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

function bootstrapTenantAwareFeatureTest(TestCase $testCase): void
{
    Event::fake([TenantCreated::class]);

    config()->set(
        'tenancy.bootstrappers',
        array_values(array_filter(
            config('tenancy.bootstrappers'),
            fn (string $bootstrapper): bool => $bootstrapper !== DatabaseTenancyBootstrapper::class,
        )),
    );

    if (! Schema::hasTable('users')) {
        $testCase->artisan('migrate', [
            '--path' => database_path('migrations/tenant/0001_01_01_000000_create_users_table.php'),
            '--realpath' => true,
        ])->assertSuccessful();
    }

    if (! Schema::hasColumn('users', 'two_factor_secret')) {
        $testCase->artisan('migrate', [
            '--path' => database_path('migrations/tenant/2025_08_14_170933_add_two_factor_columns_to_users_table.php'),
            '--realpath' => true,
        ])->assertSuccessful();
    }

    if (! Schema::hasColumn('users', 'status')) {
        $testCase->artisan('migrate', [
            '--path' => database_path('migrations/tenant/2026_02_26_145649_add_status_to_users_table.php'),
            '--realpath' => true,
        ])->assertSuccessful();
    }

    if (! Schema::hasTable('cache')) {
        $testCase->artisan('migrate', [
            '--path' => database_path('migrations/tenant/2026_02_24_134735_create_cache_table.php'),
            '--realpath' => true,
        ])->assertSuccessful();
    }

    if (! Schema::hasTable('roles')) {
        $testCase->artisan('migrate', [
            '--path' => database_path('migrations/tenant/2026_02_24_170219_create_permission_tables.php'),
            '--realpath' => true,
        ])->assertSuccessful();
    }

    $workspace = Workspace::query()->firstOrCreate([
        'name' => 'Acme Workspace',
    ]);

    $workspace->domains()->firstOrCreate([
        'domain' => 'acme',
    ]);

    URL::forceRootUrl('http://acme.tenancy-starter.test');
    $testCase->withHeader('Host', 'acme.tenancy-starter.test');
}
