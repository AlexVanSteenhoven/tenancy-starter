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
