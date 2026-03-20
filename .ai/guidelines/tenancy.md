# Tenancy

Stancl/Tenancy with domain identification. Each Workspace gets its own database, cache, filesystem, and queue scope.

- Central routes in `routes/web.php`, tenant routes in `routes/tenant.php`
- Central DB holds `workspaces` + `domains` tables; all other tables in tenant DB
- Tenant migrations in `database/migrations/tenant/`, run with `php artisan tenants:migrate`
- Do not use `DB::` for tenant data — always use `Model::query()`

**Activate `tenancy-development` skill** for full architecture, Workspace model, bootstrappers, and domain resolution.
