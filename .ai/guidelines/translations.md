# Translations

Per-domain JSON files in `lang/{locale}/**/*.json`. File path = key prefix.

- React: import `@lib/i18n` before `useTranslation()`, interpolation uses `:placeholder` (not `{{}}`)
- PHP: `__('domain.key')` with `['placeholder' => $value]`
- Key format: `<domain>.<entity>.<action|property>` — lowercase, dot-separated
- Never hardcode visible text — always use the translation system

**Activate `translations-guidelines` skill** for full file structure, naming rules, and examples.
