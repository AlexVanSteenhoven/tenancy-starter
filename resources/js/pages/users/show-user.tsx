import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

export default function ShowUser() {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('users.show-user.meta.title')} />
        </>
    );
}