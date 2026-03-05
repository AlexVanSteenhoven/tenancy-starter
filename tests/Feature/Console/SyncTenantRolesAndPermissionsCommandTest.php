<?php

declare(strict_types=1);

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\Workspace;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function (): void {
    Event::fake([TenantCreated::class]);

    config()->set(
        'tenancy.bootstrappers',
        array_values(array_filter(
            config('tenancy.bootstrappers'),
            fn (string $bootstrapper): bool => $bootstrapper !== DatabaseTenancyBootstrapper::class,
        )),
    );

    if (! Schema::hasTable('permissions')) {
        $this->artisan('migrate', [
            '--path' => database_path('migrations/tenant/2026_02_24_170219_create_permission_tables.php'),
            '--realpath' => true,
        ])->assertSuccessful();
    }
});

test('command syncs enum roles and permissions and skips existing records', function (): void {
    Workspace::query()->create(['name' => 'Acme Workspace']);
    Workspace::query()->create(['name' => 'Beta Workspace']);

    $this->artisan('sync:authorization')
        ->expectsOutputToContain('Sync complete.')
        ->assertSuccessful();

    expect(Permission::query()->count())->toBe(count(PermissionEnum::cases()));
    expect(Role::query()->count())->toBe(count(RoleEnum::cases()));

    $this->artisan('sync:authorization')
        ->expectsOutputToContain('Sync complete.')
        ->assertSuccessful();

    expect(Permission::query()->count())->toBe(count(PermissionEnum::cases()));
    expect(Role::query()->count())->toBe(count(RoleEnum::cases()));
});

test('command exits successfully when there are no tenants', function (): void {
    $this->artisan('sync:authorization')
        ->expectsOutputToContain('No tenants found to sync.')
        ->assertSuccessful();
});
