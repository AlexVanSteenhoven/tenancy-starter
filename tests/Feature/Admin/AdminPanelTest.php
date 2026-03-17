<?php

declare(strict_types=1);

use App\Enums\Permission as PermissionEnum;
use App\Models\Plan;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Laravel\Cashier\Subscription;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function (): void {
    $this->withoutVite();
    Event::fake([TenantCreated::class]);
});

test('admin login page is available on admin subdomain', function (): void {
    $response = $this->get('http://admin.tenancy-starter.test/admin/login');

    $response->assertSuccessful();
});

test('guest is redirected to admin login instead of tenant login', function (): void {
    $response = $this->get('http://admin.tenancy-starter.test/admin/workspaces');

    $response->assertRedirect('http://admin.tenancy-starter.test/admin/login');
});

test('admin user can access dashboard and workspace management pages', function (): void {
    $adminUser = createAdminUser();

    $workspace = Workspace::create([
        'name' => 'Acme Workspace',
        'plan' => 'pro',
    ]);

    $workspace->domains()->create(['domain' => 'acme']);

    Subscription::create([
        'workspace_id' => (string) $workspace->id,
        'type' => 'default',
        'stripe_id' => 'sub_123',
        'stripe_status' => 'active',
        'stripe_price' => 'price_123',
        'quantity' => 1,
    ]);

    $dashboardResponse = $this->actingAs($adminUser)
        ->get('http://admin.tenancy-starter.test/admin');

    $dashboardResponse->assertSuccessful();

    $workspacesResponse = $this->actingAs($adminUser)
        ->get('http://admin.tenancy-starter.test/admin/workspaces');

    $workspacesResponse->assertSuccessful();
});

test('admin can access plans and subscriptions overview pages', function (): void {
    $adminUser = createAdminUser();

    Plan::create([
        'slug' => 'pro',
        'name' => 'Pro',
        'price_monthly' => 2900,
        'stripe_price_id' => 'price_pro',
        'features' => [],
        'is_active' => true,
    ]);

    $plansResponse = $this->actingAs($adminUser)
        ->get('http://admin.tenancy-starter.test/admin/plans');
    $plansResponse->assertSuccessful();

    $workspace = Workspace::create([
        'name' => 'Acme Workspace',
        'plan' => 'pro',
    ]);

    Subscription::create([
        'workspace_id' => (string) $workspace->id,
        'type' => 'default',
        'stripe_id' => 'sub_123',
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro',
        'quantity' => 1,
    ]);

    $subscriptionsResponse = $this->actingAs($adminUser)
        ->get('http://admin.tenancy-starter.test/admin/subscriptions');
    $subscriptionsResponse->assertSuccessful();
});

function createAdminUser(): User
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
