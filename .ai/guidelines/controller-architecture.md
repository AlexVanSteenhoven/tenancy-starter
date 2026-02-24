# Controller Architecture

This project uses a **single-action controller + action pattern**. Every HTTP concern is separated from business logic using dedicated classes.

---

## Overview

```
Request → FormRequest (validation) → Controller (routing) → Action (business logic) → Response
```

---

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

| Concern          | Where it lives         |
|------------------|------------------------|
| Validation       | `FormRequest`          |
| Routing / HTTP   | `Controller`           |
| Business logic   | `Action`               |
| Page rendering   | `Inertia::render()`    |
| Shared rules     | `Concerns/` traits     |
