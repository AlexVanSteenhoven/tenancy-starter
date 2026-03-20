---
name: translations-guidelines
description: "Use when adding or editing translations, working with i18n keys, creating new language files, or using useTranslation/t() in React or __() in PHP. Activates when touching lang/ files or adding visible text."
---

# Translations

Shared source of truth: per-domain JSON files in `lang/{locale}/**/*.json`.

## File Structure

```
lang/
├── en/
│   ├── users.json
│   ├── onboarding.json
│   ├── pages/
│   │   ├── dashboard.json
│   │   └── settings/profile.json
│   ├── validation.json
│   └── mail.json
```

File path = translation key prefix: `users.json` → `users.*`, `pages/settings/profile.json` → `pages.settings.profile.*`.

## React Usage

```tsx
import '@lib/i18n';
import { useTranslation } from 'react-i18next';

export default function MyPage() {
    const { t } = useTranslation();
    return <h1>{t('my-page.title')}</h1>;
}
```

Interpolation uses `:placeholder` (Laravel convention), **not** `{{placeholder}}`:

```json
{ "preview": { "value": "Your workspace will be at :domain" } }
```
```tsx
t('onboarding.preview.value', { domain: 'acme.example.com' })
```

## PHP / Blade Usage

```php
__('mail.workspace.ready.subject')
__('mail.workspace.ready.title', ['workspace' => $workspace->name])
```

## Key Naming Convention

Format: `<domain>.<entity>.<action|property>` — lowercase, dot-separated.

| Pattern | Example |
|---------|---------|
| Page titles | `<domain>.meta.title` |
| Descriptions | `<domain>.meta.description` |
| Form labels | `<domain>.form.<field>.label` |
| Form placeholders | `<domain>.form.<field>.placeholder` |
| Buttons | `<domain>.actions.*` or `<domain>.form.submit` |
| Validation | `<domain>.validation.*` |
| Emails | `mail.<template>.*` |

## Rules

- Never hardcode visible text in JSX or Blade — always use the translation system
- Keep the same domain files across locales for consistency
- New languages auto-discovered by `@lib/i18n` via `import.meta.glob`
