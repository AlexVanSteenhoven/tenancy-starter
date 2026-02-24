# Tenancy

This project uses [Stancl/Tenancy](https://tenancyforlaravel.com) with **domain identification**. Each tenant (Workspace) gets its own isolated database, cache, filesystem, and queue scope.

---

## Architecture Overview

```
Central domain (tenancy-starter.test)
  └── Onboarding: user creates a Workspace
        └── Workspace gets a subdomain (e.g. acme.tenancy-starter.test)
              └── Tenant domain routes are initialised via middleware
                    └── Each request runs against the tenant's own database
```

---

## Two Types of Routes

### Central routes (`routes/web.php`)

Serve the marketing/onboarding flow on the central domain. No tenancy middleware is applied here.

```
tenancy-starter.test/
tenancy-starter.test/onboarding/create-workspace
```

### Tenant routes (`routes/tenant.php`)

All routes for authenticated workspace users. These are wrapped in:

```php
Route::middleware(['web', InitializeTenancyByDomain::class, PreventAccessFromCentralDomains::class])
```

- `InitializeTenancyByDomain` — resolves the `Workspace` from the hostname, bootstraps the tenant context
- `PreventAccessFromCentralDomains` — blocks tenant routes from being hit on the central domain

```
acme.tenancy-starter.test/dashboard
acme.tenancy-starter.test/settings/profile
```

---

## Workspace Model

The `Workspace` model is the tenant entity. It extends Stancl's `Tenant` base and lives in the central database.

```php
// app/Models/Workspace.php
final class Workspace extends Tenant implements TenantWithDatabase
{
    use HasDatabase;
    use HasDomains;

    protected $table = 'workspaces';
}
```

Custom columns beyond what the package stores in `data` JSON are declared in `getCustomColumns()` — currently `id`, `name`, and `plan`.

---

## Central Database

The central database holds:

| Table        | Purpose                              |
| ------------ | ------------------------------------ |
| `workspaces` | One row per tenant (Workspace model) |
| `domains`    | Maps subdomains → workspace IDs      |

All other tables (users, sessions, jobs, etc.) live in the **tenant database**, not central.

---

## Tenant Databases

Each workspace gets its own database, named:

```
workspace_{tenant_id}_db
```

Tenant migrations live in `database/migrations/tenant/`. Run them with:

```bash
php artisan tenants:migrate
```

To migrate a single tenant:

```bash
php artisan tenants:migrate --tenants=<workspace-id>
```

Standard `php artisan migrate` only affects the **central** database.

---

## Tenancy Bootstrappers

When a tenant request is identified, these bootstrappers fire automatically:

| Bootstrapper                    | What it does                                              |
| ------------------------------- | --------------------------------------------------------- |
| `DatabaseTenancyBootstrapper`   | Switches the default DB connection to the tenant database |
| `CacheTenancyBootstrapper`      | Scopes all cache reads/writes to the tenant               |
| `FilesystemTenancyBootstrapper` | Scopes `storage/` paths to the tenant                     |
| `QueueTenancyBootstrapper`      | Ensures queued jobs carry tenant context                  |

No manual setup is needed in controllers — after the middleware runs, all Laravel features are automatically tenant-scoped.

---

## Creating a Workspace (Onboarding Flow)

1. User visits the central domain and fills in the onboarding form.
2. `StoreOnboardingAction` runs in a transaction:
    - Creates a `Workspace` (tenant) record in the central database.
    - Creates a `Domain` record linking the subdomain to the workspace.
3. The tenant database is automatically provisioned by the package.
4. User receives an email to set up their account on `{subdomain}.tenancy-starter.test`.

---

## Writing Tenant-Aware Code

Because bootstrappers handle context switching automatically, standard Laravel patterns just work inside tenant routes:

```php
// This queries the tenant's own database — no changes needed
User::query()->where('email', $email)->first();

// Cache is scoped to this tenant automatically
Cache::put('key', $value);

// Storage paths are tenant-scoped automatically
Storage::put('avatar.jpg', $file);
```

### Do not use `DB::` for tenant data

Avoid raw `DB::` calls. They bypass Eloquent and may not respect the tenant connection switch. Always use `Model::query()`.

---

## Domain Resolution

Domains are stored in the central `domains` table and linked to a workspace:

```
domain: "acme"
tenant_id: "<workspace-uuid>"
```

The full hostname used in Herd would be `acme.tenancy-starter.test`. The `central_domains` config key lists domains that are **not** tenant-routed:

```php
// config/tenancy.php
'central_domains' => [
    'tenancy-starter.test',
],
```
