import { useTranslation } from 'react-i18next';

export function useEnumTranslation<T extends string>(map: Record<T, string>) {
    const { t } = useTranslation();

    return (value: T | string) => t(map[value as T] ?? value);
}
