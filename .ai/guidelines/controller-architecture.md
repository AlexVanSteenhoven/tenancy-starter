# Controller Architecture

Single-action controller + action pattern: `Request → FormRequest → Controller → Action → Response`

- All controllers are invokable (`__invoke()`), named `Show|Store|Update|Delete{Resource}Controller`
- Show controllers: accept `Request`, no action class, return `Response | RedirectResponse`
- Mutation controllers: accept `FormRequest` + `Action` via DI, delegate logic to action
- Scaffold with `php artisan make:feature Users/StoreUserController`

**Activate `controller-architecture` skill** for full directory structure, examples, and rules.
