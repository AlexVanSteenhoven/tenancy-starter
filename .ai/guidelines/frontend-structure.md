# Frontend Structure

Frontend lives in `resources/js/`. Always use path aliases (`@`, `@components`, `@lib`, `@hooks`, `@utils`, `@types`, `@assets`, `@styles`, `@lang`) over relative imports.

- `@components/ui/` = Shadcn/UI primitives, `@components/` = app-level composed components
- Pages in `@/pages` map to `Inertia::render()` calls
- `@/actions` and `@/routes` are auto-generated Wayfinder bindings — never edit manually

**Activate `frontend-structure` skill** for full directory overview, aliases table, and conventions.
