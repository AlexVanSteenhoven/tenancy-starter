<?php

declare(strict_types=1);

use App\Contracts\Stripe\StripePlanCatalog;
use App\Enums\Permission as PermissionEnum;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->withoutVite();
});

test('admin can sync plans from stripe as source of truth', function (): void {
    Plan::create([
        'slug' => 'pro-legacy',
        'name' => 'Pro Legacy',
        'description' => 'Old plan data',
        'price_monthly' => 2500,
        'stripe_product_id' => 'prod_keep',
        'stripe_price_id' => 'price_old',
        'features' => ['feature-a'],
        'is_active' => false,
    ]);

    Plan::create([
        'slug' => 'to-be-deleted',
        'name' => 'Remove Me',
        'price_monthly' => 1500,
        'stripe_product_id' => 'prod_removed',
        'stripe_price_id' => 'price_removed',
        'features' => [],
        'is_active' => true,
    ]);

    Plan::create([
        'slug' => 'starter-plan',
        'name' => 'Manual Plan',
        'price_monthly' => 999,
        'stripe_product_id' => null,
        'stripe_price_id' => null,
        'features' => [],
        'is_active' => true,
    ]);

    $stripeAdminService = Mockery::mock(StripePlanCatalog::class);
    $stripeAdminService
        ->shouldReceive('listProductsWithMonthlyPrices')
        ->once()
        ->andReturn([
            [
                'product_id' => 'prod_keep',
                'name' => 'Pro Plan',
                'description' => 'Latest Pro Plan',
                'slug' => 'pro',
                'is_active' => true,
                'stripe_price_id' => 'price_new',
                'price_monthly' => 4900,
            ],
            [
                'product_id' => 'prod_new',
                'name' => 'Starter Plan',
                'description' => 'Starter description',
                'slug' => null,
                'is_active' => true,
                'stripe_price_id' => 'price_starter',
                'price_monthly' => 1900,
            ],
        ]);

    $this->app->instance(StripePlanCatalog::class, $stripeAdminService);

    $response = $this
        ->actingAs(createAdminUserForPlanSync())
        ->from('http://admin.tenancy-starter.test/_/plans')
        ->post('http://admin.tenancy-starter.test/_/plans/sync');

    $response
        ->assertRedirect('http://admin.tenancy-starter.test/_/plans')
        ->assertSessionHas('status', __('admin.plans.messages.synced', [
            'created' => 1,
            'updated' => 1,
            'deleted' => 2,
        ]));

    $this->assertDatabaseHas('plans', [
        'stripe_product_id' => 'prod_keep',
        'slug' => 'pro',
        'name' => 'Pro Plan',
        'description' => 'Latest Pro Plan',
        'price_monthly' => 4900,
        'stripe_price_id' => 'price_new',
        'is_active' => 1,
    ]);

    $this->assertDatabaseHas('plans', [
        'stripe_product_id' => 'prod_new',
        'slug' => 'starter-plan',
        'name' => 'Starter Plan',
        'description' => 'Starter description',
        'price_monthly' => 1900,
        'stripe_price_id' => 'price_starter',
        'is_active' => 1,
    ]);

    $this->assertDatabaseMissing('plans', [
        'stripe_product_id' => 'prod_removed',
    ]);

    $this->assertDatabaseMissing('plans', [
        'name' => 'Manual Plan',
    ]);
});

function createAdminUserForPlanSync(): User
{
    Permission::findOrCreate(PermissionEnum::AccessAdminPanel->value, 'web');
    $role = Role::findOrCreate('sysadmin', 'web');
    $role->syncPermissions([PermissionEnum::AccessAdminPanel->value]);

    $adminUser = User::create([
        'name' => 'Admin User',
        'email' => 'admin-'.Str::uuid().'@example.com',
        'password' => 'password',
    ]);
    $adminUser->assignRole($role);

    return $adminUser;
}
