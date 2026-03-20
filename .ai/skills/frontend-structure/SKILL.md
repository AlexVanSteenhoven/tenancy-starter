---
name: frontend-structure
description: "Use when working on React/frontend files, creating new pages or components, importing with path aliases, or needing to understand the frontend directory layout. Activates when touching resources/js/ files."
---

# Frontend Structure

The frontend lives in `resources/js/`. Path aliases configured in `vite.config.ts`.

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

Always prefer aliases over relative imports.

## Directory Overview

```
resources/js/
├── actions/          # Auto-generated Wayfinder controller bindings (do not edit)
├── routes/           # Auto-generated Wayfinder named route bindings (do not edit)
├── components/
│   ├── ui/           # Primitive UI (Shadcn/UI) — no business logic
│   └── *.tsx         # App-level components (AppHeader, AppSidebar, etc.)
├── hooks/            # Custom hooks (use-*.ts / use-*.tsx)
├── layouts/          # Page layout wrappers (auth, app, etc.)
├── lib/
│   ├── i18n.ts       # i18next init — import before useTranslation
│   └── utils.ts      # cn() class helper, toUrl() helper
├── pages/            # Inertia page components — one file per route
├── types/            # TypeScript types (re-exported from index.ts)
└── wayfinder/        # Wayfinder index (auto-generated)
```

## Quick Reference

```tsx
import { Button } from '@components/ui/button';       // UI primitives
import InputError from '@components/input-error';      // App components
import { useInitials } from '@hooks/use-initials';     // Hooks
import { cn } from '@lib/utils';                       // Utilities
import type { User } from '@types';                    // Types
```

## Pages

File paths map to `Inertia::render()`:
```
resources/js/pages/auth/setup-account.tsx → Inertia::render('auth/setup-account')
```

Pages must import `@lib/i18n` and call `useTranslation()` for all visible text.

## Wayfinder (`@/actions`, `@/routes`)

Auto-generated — never edit manually:
```tsx
import StoreSetupAccountController from '@/actions/App/Http/Controllers/Onboarding/StoreSetupAccountController';
<Form {...StoreSetupAccountController.form()} />
```
