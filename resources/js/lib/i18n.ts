import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

type TranslationMap = Record<string, unknown>;
type LanguageModule = { default: TranslationMap };
type LanguageResources = Record<string, { translation: TranslationMap }>;

const languageModules = import.meta.glob<LanguageModule>('@lang/*.json', {
    eager: true,
});
const languages: LanguageResources = {};

for (const path in languageModules) {
    const match = path.match(/\/([\w-]+)\.json$/);

    if (match) {
        const language = match[1];
        languages[language] = { translation: languageModules[path].default };
    }
}

i18n.use(initReactI18next).init({
    resources: { ...languages },
    lng: 'en',
    fallbackLng: 'en',

    interpolation: {
        escapeValue: false,
        prefix: ':',
        suffix: '',
        prefixEscaped: ':',
        suffixEscaped: '(?=[^a-zA-Z0-9_]|$)',
    },
});

export default i18n;
