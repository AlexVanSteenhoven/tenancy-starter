# Translations

This project supports both React (Inertia) and Laravel (Blade/PHP) translation consumers. The shared source of truth is a single JSON file.

---

## File Structure

```
lang/
├── en.json          # React + shared translations (primary source)
└── en/
    ├── mail.php     # PHP-only strings (email templates, Blade mails)
    └── onboarding.php  # PHP-only strings (validation messages used server-side)
```

### Rule: JSON first

- Use `lang/en.json` for anything consumed by React.
- Use `lang/en/*.php` only for strings that are never needed in React (e.g. email subject lines, server-side validation messages).
- Never duplicate a key in both JSON and PHP.

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

The `@lib/i18n` module auto-discovers all `lang/*.json` files at build time via `import.meta.glob`. No manual registration is needed when adding a new language.

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

1. Add the English string to `lang/en.json` under the correct domain key.
2. Use `t('the.key')` in React, or `__('the.key')` in PHP/Blade.
3. Never hardcode visible text in JSX or Blade — always use the translation system.

---

## Adding a New Language

Drop a new JSON file into `lang/` (e.g. `lang/nl.json`). The i18n initialisation in `@lib/i18n` will pick it up automatically. No code changes required.
