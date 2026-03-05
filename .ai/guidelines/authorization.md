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
