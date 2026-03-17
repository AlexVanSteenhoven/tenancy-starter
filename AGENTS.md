<laravel-boost-guidelines>
=== .ai/authorization rules ===

# Authorization Guideline: Roles vs Permissions

## Core Principle

Design authorization around **permissions**, not roles.

Roles are only used to **group permissions**. The application should always check permissions.

---

## Architecture Overview

```
User → Role(s) → Permission(s)
```

- Users are assigned roles
- Roles contain permissions
- The application checks permissions

---

## Enums (Source of Truth)

All roles and permissions are defined as enums:

```php
App\Enums\Role
App\Enums\Permission
```

### Rules

- Enums are the **single source of truth**
- No hardcoded strings in the application
- Enums map to database values
- Naming must be consistent and predictable

### Example

```php
enum Permission: string
{
    case CreateUser = 'create:users';
    case UpdateUser = 'update:users';
    case DeleteUser = 'delete:users';
}
```

---

## Permission Naming Convention

Format:

```
[action]:[resource]
```

### Examples

- `create:users`
- `view:users`
- `update:users`
- `delete:users`

### Rules

- Always use **plural resources**
- Keep actions consistent: `view`, `create`, `update`, `delete`
- Avoid vague permissions

---

## Roles

### Purpose

Roles group permissions into logical sets.

### Rules

- Assign roles only to users
- Do not rely on roles for authorization checks
- Role names are flexible and can change

### Example

```php
$user->assignRole(Role::Admin);
```

---

## Permissions

### Purpose

Permissions define what actions are allowed.

### Rules

- Assign permissions to roles only
- Keep permissions granular
- Treat permissions as static

### Example

```php
$role->givePermissionTo(Permission::CreateUser);
```

---

## Users

### Rules

- Users inherit permissions via roles
- Avoid direct permission assignment

### Anti-pattern

```php
$user->givePermissionTo(Permission::CreateUser); // ❌ avoid
```

### Preferred

```php
$role->givePermissionTo(Permission::CreateUser);
$user->assignRole($role);
```

---

## Authorization Usage

Always check permissions across the application.

### Controllers

```php
$this->authorize(Permission::CreateUser->value);
```

### Policies

```php
return $user->can(Permission::UpdateUser->value);
```

### Middleware

```php
->middleware('permission:' . Permission::ViewUser->value)
```

### Routes

```php
Route::get('/users', ...)
    ->middleware('permission:' . Permission::ViewUser->value);
```

---

## Frontend (React + Inertia)

### Strategy

- Backend remains the **source of truth**
- Frontend only uses permissions for **UI control**

### Example

In the backend controller

```php
public function __invoke(Request $request) {
    return Inertia::render(
        component: 'users/show-users',
        props: [
            'users' => $users,
            'canInviteUsers' => $request->user()?->hasPermissionTo(permission: Permission::InviteMembers) ?? false,
        ],
    );
}
```

Then use it on the frontend:

```tsx
const { canInviteUsers } = usePage<{ canInviteUsers: boolean }>().props;

return (
    <>
        {/* Option 1 */}
        {canInviteUsers ? (
            <h1>I can invite users</h1>
        ) : (
            <h1>I do not have the permission to invite users</h1>
        )}

        {/* Option 2 */}
        {canInviteUsers && <h1>I can invite users</h1>}
    </>
);
```

### Rules

- Never trust frontend authorization
- Always enforce permissions on backend

---

## When Role Checks Are Acceptable

Use role checks only when:

- Grouping large route sections
- System-level access control

### Example

```php
->middleware('role:' . Role::Admin->value)
```

---

## Best Practices

- Always check permissions, not roles
- Keep permissions granular
- Use enums everywhere
- Avoid duplication of permission strings
- Keep backend authoritative

---

## Summary

- Users have roles
- Roles have permissions
- Application checks permissions
- Permissions follow `[action]:[resource]`
- Enums are the single source of truth

---

## Checklist

- [ ] Permissions are defined as enums
- [ ] Naming follows `[action]:[resource]`
- [ ] No direct permissions assigned to users
- [ ] Authorization uses permissions everywhere
- [ ] Frontend only reflects backend authorization

=== .ai/controller-architecture rules ===

# Controller Architecture

This project uses a **single-action controller + action pattern**. Every HTTP concern is separated from business logic using dedicated classes.

---

## Overview

```
Request → FormRequest (validation) → Controller (routing) → Action (business logic) → Response
```

---

## Create new feature

For easier and faster generating a base feature we will use a custom artisan command (make:feature), this scaffolds the files for us.

```
php artisan make:feature Users/ShowUsersController # generates the controller and view

php artisan make:feature Users/StoreUserController # generates the controller, action and form request

```

`make:feature` behavior:

- Use `/` in the name to create nested folders, where the last segment is the class file name.
    - Example: `Users/Admin/ShowUsersController` creates files under `Users/Admin`.
- If the controller name starts with `Show`, `List`, `Show`, or `Index`, it scaffolds:
    - `app/Http/Controllers/...`
    - `resources/js/pages/...` (kebab-case path from the full feature path)
- For non-show controllers, it scaffolds:
    - `app/Http/Controllers/...` from a command stub
    - `app/Http/Requests/...` via `php artisan make:request`
    - `app/Actions/...` via `php artisan make:action`

## Invokable Controllers

All controllers are **single-action** (invokable) using `__invoke()`. Each controller maps 1:1 to a route and is named after the HTTP verb + resource:

```
Show{Resource}Controller  → GET  (renders a page or redirects)
Store{Resource}Controller → POST (creates a resource)
Update{Resource}Controller → PUT/PATCH (updates a resource)
Delete{Resource}Controller → DELETE (removes a resource)
```

### Directory Structure

Controllers are grouped by domain, matching the URL structure:

```
app/Http/Controllers/
├── Settings/
│   ├── Profile/
│   │   ├── ShowProfileController.php
│   │   ├── UpdateProfileController.php
│   │   └── DeleteProfileController.php
│   └── Password/
│       ├── ShowPasswordController.php
│       └── UpdatePasswordController.php
└── Onboarding/
    ├── ShowSetupAccountController.php
    └── StoreSetupAccountController.php
```

---

## Rules

### Show controllers (read-only)

- Accept `Request` (not a custom FormRequest)
- No action class — logic is minimal (guard checks, data fetching)
- Return `Response | RedirectResponse`

```php
final class ShowProfileController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('settings/profile', [
            'user' => $request->user(),
        ]);
    }
}
```

### Mutation controllers (write operations)

- Accept a **FormRequest** specific to this action
- Accept an **Action** class via dependency injection
- Delegate all business logic to the action
- Only handle the HTTP response after the action completes

```php
final class UpdateProfileController extends Controller
{
    public function __invoke(ProfileUpdateRequest $request, ProfileUpdateAction $action): RedirectResponse
    {
        $action->handle(request: $request);

        return to_route('profile.edit');
    }
}
```

---

## Form Requests

Every mutation controller has a dedicated `FormRequest`. It lives in a directory mirroring the controller:

```
app/Http/Requests/
├── Settings/
│   ├── ProfileUpdateRequest.php
│   └── ProfileDeleteRequest.php
└── Onboarding/
    └── StoreSetupAccountRequest.php
```

Rules:

- Always `final`
- Always declare `rules(): array` with a typed return `array<string, ValidationRule|array<mixed>|string>`
- Share common rules via traits (`use ProfileValidationRules;`)
- Authorization logic (if needed) belongs in `authorize()`, not the controller

---

## Actions

Actions contain all business logic and are injected into mutation controllers. They live in:

```
app/Actions/
├── Settings/
│   ├── ProfileUpdateAction.php
│   └── ProfileDeleteAction.php
└── Onboarding/
    └── StoreOnboardingAction.php
```

Rules:

- Always `final` (and `readonly` when stateless)
- Single public `handle()` method — accepts the typed FormRequest, returns `void`
- Business logic only — no HTTP knowledge, no redirects, no response building
- Wrap side effects in `DB::transaction()` when touching the database

```php
final readonly class ProfileDeleteAction
{
    public function handle(ProfileDeleteRequest $request): void
    {
        DB::transaction(function () use ($request): void {
            $user = $request->user();

            Auth::logout();
            $user->delete();

            $request->session()->invalidate();
            $request->session()->regenerateToken();
        });
    }
}
```

---

## Simple vs. Action Controllers

Not every mutation needs an action. For trivial one-liners (e.g. creating a single model with no side effects), inline logic in the controller is acceptable. Use an action when:

- The operation has multiple steps
- Side effects exist (email, events, jobs, transactions)
- The logic could be reused elsewhere (e.g. CLI commands, jobs)

---

## Summary Table

| Concern        | Where it lives      |
| -------------- | ------------------- |
| Validation     | `FormRequest`       |
| Routing / HTTP | `Controller`        |
| Business logic | `Action`            |
| Page rendering | `Inertia::render()` |
| Shared rules   | `Concerns/` traits  |

=== .ai/frontend-structure rules ===

# Frontend Structure

The frontend lives entirely in `resources/js/`. Path aliases are configured in `vite.config.ts` for clean, consistent imports across the project.

---

## Path Aliases

| Alias         | Resolves to                    | Purpose                                      |
|---------------|--------------------------------|----------------------------------------------|
| `@`           | `resources/js`                 | Root — pages, layouts, actions, routes, types |
| `@components` | `resources/js/components`      | Shared React components                      |
| `@lib`        | `resources/js/lib`             | Utility libraries (i18n setup, cn helper)    |
| `@hooks`      | `resources/js/hooks`           | Custom React hooks                           |
| `@utils`      | `resources/js/utils`           | Pure utility functions                       |
| `@types`      | `resources/js/types`           | TypeScript types and interfaces              |
| `@assets`     | `resources/assets`             | Static assets (images, icons, fonts)         |
| `@styles`     | `resources/css`                | Global CSS / Tailwind entry point            |
| `@lang`       | `lang`                         | Translation JSON files                       |

Always prefer these aliases over relative imports.

---

## Directory Overview

```
resources/js/
├── actions/          # Auto-generated Wayfinder controller bindings (do not edit)

├── routes/           # Auto-generated Wayfinder named route bindings (do not edit)

├── components/
│   ├── ui/           # Primitive UI components (Button, Input, Label, etc.)

│   └── *.tsx         # Composed app-level components (AppHeader, AppSidebar, etc.)

├── hooks/            # Custom React hooks (use-*.ts / use-*.tsx)

├── layouts/          # Page layout wrappers (auth, app, etc.)

├── lib/
│   ├── i18n.ts       # i18next initialisation — import this before useTranslation

│   └── utils.ts      # cn() class helper, toUrl() helper

├── pages/            # Inertia page components — one file per route

├── types/            # TypeScript type definitions

│   ├── index.ts      # Re-exports all types

│   ├── auth.ts
│   ├── navigation.ts
│   └── ui.ts
└── wayfinder/        # Wayfinder index (auto-generated)

```

---

## Component Conventions

### `@components/ui/` — Primitive UI

Unstyled or lightly styled atomic components from Shadcn/UI. These are building blocks — do not add business logic here.

```tsx
import { Button } from '@components/ui/button';
import { Input } from '@components/ui/input';
import { Label } from '@components/ui/label';
```

### `@components/` — App-level components

Composed components that assemble primitives and carry application context (navigation, sidebars, headers). Always check here before building a new component.

```tsx
import InputError from '@components/input-error';
import { Spinner } from '@components/ui/spinner';
```

---

## Hooks (`@hooks`)

Custom React hooks follow the `use-*.ts` naming convention. All hooks are in a flat directory.

```tsx
import { useInitials } from '@hooks/use-initials';
import { useMobile } from '@hooks/use-mobile';
```

---

## Utility Libraries (`@lib`)

### `@lib/utils`

Contains `cn()` for merging Tailwind classes, and `toUrl()` for normalising Inertia link hrefs.

```tsx
import { cn } from '@lib/utils';

<div className={cn('base-class', isActive && 'active-class')} />
```

### `@lib/i18n`

Initialises i18next. Must be imported once before `useTranslation` is used in a component tree — typically at the top of a page file.

```tsx
import '@lib/i18n';
import { useTranslation } from 'react-i18next';
```

---

## Types (`@types`)

All application types are re-exported from `@types/index.ts`. Add new types to the relevant domain file and re-export through the index.

```tsx
import type { User } from '@types';
```

---

## Pages (`@/pages`)

Inertia page components. File paths map directly to `Inertia::render()` calls on the server.

```
resources/js/pages/auth/setup-account.tsx
→ Inertia::render('auth/setup-account')
```

Pages should import `@lib/i18n` and call `useTranslation()` for all visible text.

---

## Wayfinder (`@/actions`, `@/routes`)

Auto-generated — never edit these files manually. Import controller bindings for type-safe route calls:

```tsx
import StoreSetupAccountController from '@/actions/App/Http/Controllers/Onboarding/StoreSetupAccountController';

<Form {...StoreSetupAccountController.form()} />
```

=== .ai/generics rules ===

## General code instructions

- Don't generate code comments above the methods or code blocks if they are obvious. Don't add docblock comments when defining variables, unless instructed to, like `/** @var \App\Models\User $currentUser */`. Generate comments only for something that needs extra explanation for the reasons why that code was written.
- For new features, you MUST generate Pest automated tests.
- For library documentation, if some library is not available in Laravel Boost 'search-docs', always use context7. Automatically use the Context7 MCP tools to resolve library id and the get library docs without me having to explicitly ask.

---

## PHP instructions

- In PHP, use `match` operator over `switch` whenever possible
- Generate Enums always in the folder `app/Enums`, not in the main `app/` folder, unless instructed differently.
- Always use Enum value as the default in the migration if column values are from the enum. Always casts this column to the enum type in the Model.
- Don't create temporary variables like `$currentUser = auth()->user()` if that variable is used only one time.
- Always use Enum where possible instead of hardcoded string values, if Enum class exists. For example, in Blade files, and in the tests when creating data if field is casted to Enum then use that Enum instead of hardcoding the value.

---

## Laravel instructions

- **Eloquent Observers** should be registered in Eloquent Models with PHP Attributes, and not in AppServiceProvider. Example: `#[ObservedBy([UserObserver::class])]` with `use Illuminate\Database\Eloquent\Attributes\ObservedBy;` on top
- Use Laravel helpers instead of `use` section classes. Examples: use `auth()->id()` instead of `Auth::id()` and adding `Auth` in the `use` section. Other examples: use `redirect()->route()` instead of `Redirect::route()`, or `str()->slug()` instead of `Str::slug()`.
- Don't use `whereKey()` or `whereKeyNot()`, use specific fields like `id`. Example: instead of `->whereKeyNot($currentUser->getKey())`, use `->where('id', '!=', $currentUser->id)`.
- Don't add `::query()` when running Eloquent `create()` statements. Example: instead of `User::query()->create()`, use `User::create()`.
- When adding columns in a migration, update the model's `$fillable` array to include those new attributes.
- Never chain multiple migration-creating commands (e.g., `make:model -m`, `make:migration`) with `&&` or `;` — they may get identical timestamps. Run each command separately and wait for completion before running the next.
- Enums: If a PHP Enum exists for a domain concept, always use its cases (or their `->value`) instead of raw strings everywhere — routes, middleware, migrations, seeds, configs, and UI defaults.
- Don't create Controllers with just one method which just returns `view()`. Instead, use `Route::view()` with Blade file directly.
- Always use Laravel's @session() directive instead of @if(session()) for displaying flash messages in Blade templates.
- In Blade files always use `@selected()` and `@checked()` directives instead of `selected` and `checked` HTML attributes. Good example: @selected(old('status') === App\Enums\ProjectStatus::Pending->value). Bad example: {{ old('status') === App\Enums\ProjectStatus::Pending->value ? 'selected' : '' }}.

---

## Testing instructions

### Before Writing Tests

1. **Check database schema** - Use `database-schema` tool to understand:
    - Which columns have defaults
    - Which columns are nullable
    - Foreign key relationship names

2. **Verify relationship names** - Read the model file to confirm:
    - Exact relationship method names (not assumed from column names)
    - Return types and related models

3. **Test realistic states** - Don't assume:
    - Empty model = all nulls (check for defaults)
    - `user_id` foreign key = `user()` relationship (could be `author()`, `employer()`, etc.)
    - When testing form submissions that redirect back with errors, assert that old input is preserved using `assertSessionHasOldInput()`.

---

=== .ai/tenancy rules ===

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

=== .ai/translations rules ===

# Translations

This project supports both React (Inertia) and Laravel (Blade/PHP) translation consumers. The shared source of truth is per-domain JSON files.

---

## File Structure

```
lang/
├── en/
│   ├── users.json
│   ├── onboarding.json
│   ├── pages/
│   │   ├── dashboard.json
│   │   └── settings/
│   │       └── profile.json
│   ├── validation.json
│   └── mail.json
└── nl/
    ├── users.json
    ├── onboarding.json
    ├── pages/
    │   ├── dashboard.json
    │   └── settings/
    │       └── profile.json
    ├── validation.json
    └── mail.json
```

### Rule: JSON first

- Use `lang/{locale}/**/*.json` for all user-facing strings.
- The file path (including nested directories) is the translation key prefix.
- Example: `users.json` -> `users.*`, `pages/settings/profile.json` -> `pages.settings.profile.*`.
- Keep the same domain files across locales for consistency.

---

## React Usage

### Setup

Every page that uses translations must import the i18n initialisation before calling `useTranslation`:

```tsx
import '@lib/i18n';
import { useTranslation } from 'react-i18next';

export default function MyPage() {
    const { t } = useTranslation();

    return <h1>{t('my-page.title')}</h1>;
}
```

The `@lib/i18n` module auto-discovers all `lang/{locale}/**/*.json` files at build time via `import.meta.glob`. No manual registration is needed when adding a new language.

### Interpolation

The interpolation syntax in this project uses `:placeholder` (matching Laravel's convention), **not** `{{placeholder}}`:

```json
"preview": {
    "value": "Your workspace will be at :domain"
}
```

```tsx
t('onboarding.preview.value', { domain: 'acme.example.com' })
```

---

## PHP / Blade Usage

For server-side strings in PHP files, Blade views, and mail templates:

```php
__('mail.workspace.ready.subject')
__('onboarding.validation.subdomain_taken')
```

With interpolation:

```php
__('mail.workspace.ready.title', ['workspace' => $workspace->name])
```

---

## Key Naming Convention

Keys follow a **domain-first** dot-separated structure:

```
<domain>.<entity>.<action|property>
```

| Example key                              | Usage                        |
|------------------------------------------|------------------------------|
| `setup.account.meta.title`               | Page title                   |
| `setup.account.form.email.label`         | Form field label             |
| `onboarding.form.submit`                 | Submit button text           |
| `onboarding.validation.subdomain_taken`  | Server-side validation error |
| `mail.workspace.ready.subject`           | Email subject line           |

### Rules

- Lowercase, dot-separated — never underscores in the key hierarchy
- Buttons → `<domain>.actions.*` or `<domain>.form.submit`
- Page titles → `<domain>.meta.title`
- Descriptions → `<domain>.meta.description`
- Form labels → `<domain>.form.<field>.label`
- Form placeholders → `<domain>.form.<field>.placeholder`
- Validation → `<domain>.validation.*`
- Emails → `mail.<template>.*`

---

## Adding a New Translation

1. Add the English string to the correct domain file in `lang/en/**/*.json`.
2. Use `t('the.key')` in React, or `__('the.key')` in PHP/Blade.
3. Never hardcode visible text in JSX or Blade — always use the translation system.

---

## Adding a New Language

Create a locale directory (for example `lang/nl/`) and add the same domain JSON files (`users.json`, `validation.json`, etc.). The i18n initialisation in `@lib/i18n` will pick it up automatically. No code changes required.

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.18
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v2
- laravel/cashier (CASHIER) - v16
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/wayfinder (WAYFINDER) - v0
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- rector/rector (RECTOR) - v2
- @inertiajs/react (INERTIA_REACT) - v2
- react (REACT) - v19
- tailwindcss (TAILWINDCSS) - v4
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `wayfinder-development` — Activates whenever referencing backend routes in frontend components. Use when importing from @/actions or @/routes, calling Laravel routes from TypeScript, or working with Wayfinder route functions.
- `pest-testing` — Tests applications using the Pest 4 PHP framework. Activates when writing tests, creating unit or feature tests, adding assertions, testing Livewire components, browser testing, debugging test failures, working with datasets or mocking; or when the user mentions test, spec, TDD, expects, assertion, coverage, or needs to verify functionality works.
- `inertia-react-development` — Develops Inertia.js v2 React client-side applications. Activates when creating React pages, forms, or navigation; using &lt;Link&gt;, &lt;Form&gt;, useForm, or router; working with deferred props, prefetching, or polling; or when user mentions React with Inertia, React pages, React forms, or React navigation.
- `tailwindcss-development` — Styles applications using Tailwind CSS v4 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes.
- `developing-with-fortify` — Laravel Fortify headless authentication backend development. Activate when implementing authentication features including login, registration, password reset, email verification, two-factor authentication (2FA/TOTP), profile updates, headless auth, authentication scaffolding, or auth guards in Laravel applications.
- `laravel-permission-development` — Build and work with Spatie Laravel Permission features, including roles, permissions, middleware, policies, teams, and Blade directives.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `pnpm run build`, `pnpm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->
```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- That being said, keys in an Enum should follow existing application Enum conventions.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd and will be available at: `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs for the user.
- You must not run any commands to make the site available via HTTP(S). It is always available through Laravel Herd.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v2

- Use all Inertia features from v1 and v2. Check the documentation before making changes to ensure the correct approach.
- New features: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `pnpm run build` or ask the user to run `pnpm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== wayfinder/core rules ===

# Laravel Wayfinder

Wayfinder generates TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

- IMPORTANT: Activate `wayfinder-development` skill whenever referencing backend routes in frontend components.
- Invokable Controllers: `import StorePost from '@/actions/.../StorePostController'; StorePost()`.
- Parameter Binding: Detects route keys (`{post:slug}`) — `show({ slug: "my-post" })`.
- Query Merging: `show(1, { mergeQuery: { page: 2, sort: null } })` merges with current URL, `null` removes params.
- Inertia: Use `.form()` with `<Form>` component or `form.submit(store())` with useForm.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.
- CRITICAL: ALWAYS use `search-docs` tool for version-specific Pest documentation and updated code examples.
- IMPORTANT: Activate `pest-testing` every time you're working with a Pest or testing-related task.

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation and updated code examples. Never rely on training data.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.

=== laravel/fortify rules ===

# Laravel Fortify

- Fortify is a headless authentication backend that provides authentication routes and controllers for Laravel applications.
- IMPORTANT: Always use the `search-docs` tool for detailed Laravel Fortify patterns and documentation.
- IMPORTANT: Activate `developing-with-fortify` skill when working with Fortify authentication features.

</laravel-boost-guidelines>
