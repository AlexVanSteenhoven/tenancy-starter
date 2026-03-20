---
name: authorization-guidelines
description: "Use when working with roles, permissions, authorization checks, middleware guards, policies, or Spatie Laravel Permission. Activates when adding permission checks to controllers, routes, or frontend components."
---

# Authorization: Roles vs Permissions

## Core Principle

Design authorization around **permissions**, not roles. Roles only group permissions. The application always checks permissions.

```
User → Role(s) → Permission(s)
```

## Enums (Source of Truth)

All roles and permissions defined as enums — single source of truth:

```php
App\Enums\Role
App\Enums\Permission
```

- No hardcoded strings in the application
- Enums map to database values

```php
enum Permission: string
{
    case CreateUser = 'create:users';
    case UpdateUser = 'update:users';
    case DeleteUser = 'delete:users';
}
```

## Permission Naming: `[action]:[resource]`

- Always **plural resources**: `create:users`, `view:users`, `update:users`, `delete:users`
- Consistent actions: `view`, `create`, `update`, `delete`
- No vague permissions

## Roles

- Roles group permissions into logical sets
- Assign roles only to users
- Never check roles for authorization — only permissions
- Role names are flexible and can change

```php
$user->assignRole(Role::Admin);
```

## Permissions

- Assign permissions to roles only (never directly to users)
- Keep permissions granular and static

```php
// ✅ Preferred
$role->givePermissionTo(Permission::CreateUser);
$user->assignRole($role);

// ❌ Anti-pattern
$user->givePermissionTo(Permission::CreateUser);
```

## Authorization Usage

### Controllers
```php
$this->authorize(Permission::CreateUser->value);
```

### Policies
```php
return $user->can(Permission::UpdateUser->value);
```

### Middleware / Routes
```php
->middleware('permission:' . Permission::ViewUser->value)
```

## Frontend (React + Inertia)

Backend is source of truth. Frontend only uses permissions for UI control:

```php
// Controller
return Inertia::render('users/show-users', [
    'canInviteUsers' => $request->user()?->hasPermissionTo(Permission::InviteMembers) ?? false,
]);
```

```tsx
// Component
const { canInviteUsers } = usePage<{ canInviteUsers: boolean }>().props;
{canInviteUsers && <InviteButton />}
```

- Never trust frontend authorization
- Always enforce permissions on backend

## When Role Checks Are Acceptable

Only for grouping large route sections or system-level access control:

```php
->middleware('role:' . Role::Admin->value)
```
