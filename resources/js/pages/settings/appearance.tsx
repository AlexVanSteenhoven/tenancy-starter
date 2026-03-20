import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import SettingsLayout from '@/layouts/settings/layout';
import { edit as editAppearance } from '@/routes/settings/appearance';
import AppearanceTabs from '@components/appearance-tabs';
import Heading from '@components/heading';
import { LayoutResolver } from '@components/layout-resolver';
import type { BreadcrumbItem } from '@types';
import '@lib/i18n';

export default function Appearance() {
    const { t } = useTranslation();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('settings.appearance.meta.title'),
            href: editAppearance().url,
        },
    ];

    return (
        <LayoutResolver breadcrumbs={breadcrumbs}>
            <Head title={t('settings.appearance.meta.title')} />

            <h1 className="sr-only">
                {t('settings.appearance.meta.sr-title')}
            </h1>

            <SettingsLayout>
                <div className="space-y-6">
                    <Heading
                        variant="small"
                        title={t('settings.appearance.heading.title')}
                        description={t(
                            'settings.appearance.heading.description',
                        )}
                    />
                    <AppearanceTabs />
                </div>
            </SettingsLayout>
        </LayoutResolver>
    );
}
