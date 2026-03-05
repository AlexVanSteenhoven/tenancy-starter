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
