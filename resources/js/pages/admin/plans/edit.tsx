import { Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import ShowPlansController from '@/actions/App/Http/Controllers/Admin/Plans/ShowPlansController';
import AdminLayout from '@/layouts/admin-layout';
import '@lib/i18n';

export default function AdminPlanEdit() {
    const { t } = useTranslation();

    return (
        <AdminLayout>
            <Head title={t('admin.plans.meta.title')} />
            <div className="space-y-4">
                <h1 className="text-2xl font-semibold">{t('admin.plans.meta.title')}</h1>
                <p className="text-sm text-muted-foreground">
                    {t('admin.plans.meta.description')}
                </p>
                <Link href={ShowPlansController.url()} className="text-primary hover:underline">
                    {t('admin.common.view')}
                </Link>
            </div>
        </AdminLayout>
    );
}
