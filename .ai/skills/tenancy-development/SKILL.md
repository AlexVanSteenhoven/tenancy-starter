---
name: tenancy-development
description: "Use when working with workspaces, tenant routing, domains, tenant databases, central vs tenant migrations, or the Stancl/Tenancy package. Activates when touching routes/tenant.php, the Workspace model, or multi-database concerns."
---

# Tenancy

This project uses [Stancl/Tenancy](https://tenancyforlaravel.com) with **domain identification**. Each tenant (Workspace) gets its own isolated database, cache, filesystem, and queue scope.

## Architecture

```
Central domain (tenancy-starter.test)
  └── Onboarding: user creates a Workspace
        └── Workspace gets a subdomain (e.g. acme.tenancy-starter.test)
              └── Tenant domain routes initialised via middleware
                    └── Each request runs against the tenant's own database
```

## Route Types

### Central routes (`routes/web.php`)
Marketing/onboarding flow on the central domain. No tenancy middleware.

### Tenant routes (`routes/tenant.php`)
Authenticated workspace users. Wrapped in:
```php
Route::middleware(['web', InitializeTenancyByDomain::class, PreventAccessFromCentralDomains::class])
```

## Workspace Model

The tenant entity. Extends Stancl's `Tenant` base, lives in central database.

```php
final class Workspace extends Tenant implements TenantWithDatabase
{
    use HasDatabase;
    use HasDomains;
    protected $table = 'workspaces';
}
```

Custom columns declared in `getCustomColumns()` — currently `id`, `name`, and `plan`.

## Databases

### Central database
| Table        | Purpose                              |
| ------------ | ------------------------------------ |
| `workspaces` | One row per tenant (Workspace model) |
| `domains`    | Maps subdomains → workspace IDs      |

All other tables (users, sessions, jobs) live in **tenant databases**.

### Tenant databases
Named `workspace_{tenant_id}_db`. Migrations in `database/migrations/tenant/`.

```bash
php artisan tenants:migrate                          # all tenants
php artisan tenants:migrate --tenants=<workspace-id> # single tenant
php artisan migrate                                  # central only
```

## Bootstrappers (automatic)

| Bootstrapper                    | What it does                                              |
| ------------------------------- | --------------------------------------------------------- |
| `DatabaseTenancyBootstrapper`   | Switches default DB connection to tenant database         |
| `CacheTenancyBootstrapper`      | Scopes cache reads/writes to tenant                       |
| `FilesystemTenancyBootstrapper` | Scopes `storage/` paths to tenant                         |
| `QueueTenancyBootstrapper`      | Ensures queued jobs carry tenant context                  |

No manual setup needed — after middleware runs, all Laravel features are tenant-scoped.

## Writing Tenant-Aware Code

Standard Laravel patterns just work inside tenant routes:

```php
User::query()->where('email', $email)->first(); // tenant's own database
Cache::put('key', $value);                       // scoped to tenant
Storage::put('avatar.jpg', $file);               // scoped to tenant
```

**Do not use `DB::`** for tenant data — it may not respect the tenant connection switch. Always use `Model::query()`.

## Domain Resolution

Domains stored in central `domains` table. Full hostname: `acme.tenancy-starter.test`.

```php
// config/tenancy.php
'central_domains' => ['tenancy-starter.test'],
```

## Creating a Workspace (Onboarding Flow)

1. User fills onboarding form on central domain
2. `StoreOnboardingAction` in transaction: creates Workspace + Domain
3. Tenant database auto-provisioned by package
4. User receives email to set up account on `{subdomain}.tenancy-starter.test`
