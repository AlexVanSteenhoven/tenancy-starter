import { roleTranslationMap } from '@/types/enums';
import { useEnumTranslation } from '@hooks/use-enum-translation';

export function useLabel() {
    const translateRole = useEnumTranslation(roleTranslationMap);

    const getLabel = <T extends string>(
        value: T | string | null | undefined,
        translator: (value: T | string) => string,
    ): string => {
        if (!value) {
            return '';
        }

        return translator(value);
    };

    return { getLabel, translateRole };
}
