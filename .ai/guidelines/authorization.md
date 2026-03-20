# Authorization

Design authorization around **permissions**, not roles. Roles only group permissions.

- Permissions defined as enums in `App\Enums\Permission` — single source of truth
- Permission naming: `[action]:[resource]` (e.g. `create:users`, `view:users`)
- Always check permissions, never roles (except for grouping large route sections)
- Frontend only reflects backend authorization — backend is source of truth

**Activate `authorization-guidelines` skill** for full patterns, examples, and frontend integration.
