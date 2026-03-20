---
name: controller-architecture
description: "Use when creating controllers, actions, form requests, or scaffolding new features. Activates when working with make:feature, invokable controllers, FormRequest classes, or Action classes."
---

# Controller Architecture

This project uses a **single-action controller + action pattern**. Every HTTP concern is separated from business logic using dedicated classes.

## Overview

```
Request → FormRequest (validation) → Controller (routing) → Action (business logic) → Response
```

## Scaffolding New Features

Use the custom artisan command:

```
php artisan make:feature Users/ShowUsersController   # controller + view
php artisan make:feature Users/StoreUserController   # controller + action + form request
```

`make:feature` behavior:
- Use `/` to create nested folders, last segment = class name
- `Show`/`List`/`Index` prefix → scaffolds controller + `resources/js/pages/...` (kebab-case)
- Other prefixes → scaffolds controller + FormRequest + Action

## Controller Naming

```
Show{Resource}Controller  → GET  (renders a page or redirects)
Store{Resource}Controller → POST (creates a resource)
Update{Resource}Controller → PUT/PATCH (updates a resource)
Delete{Resource}Controller → DELETE (removes a resource)
```

Controllers grouped by domain matching URL structure:

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

## Show Controllers (read-only)

- Accept `Request` (not a custom FormRequest)
- No action class — minimal logic (guard checks, data fetching)
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

## Mutation Controllers (write operations)

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

## Form Requests

Mirrored directory structure to controllers:

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
- Always declare `rules(): array` with typed return `array<string, ValidationRule|array<mixed>|string>`
- Share common rules via traits (`use ProfileValidationRules;`)
- Authorization logic (if needed) belongs in `authorize()`, not the controller

## Actions

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

## When to Use Actions

Not every mutation needs an action. For trivial one-liners, inline logic is acceptable. Use an action when:
- The operation has multiple steps
- Side effects exist (email, events, jobs, transactions)
- The logic could be reused elsewhere (CLI commands, jobs)

## Quick Reference

| Concern        | Where it lives      |
| -------------- | ------------------- |
| Validation     | `FormRequest`       |
| Routing / HTTP | `Controller`        |
| Business logic | `Action`            |
| Page rendering | `Inertia::render()` |
| Shared rules   | `Concerns/` traits  |
