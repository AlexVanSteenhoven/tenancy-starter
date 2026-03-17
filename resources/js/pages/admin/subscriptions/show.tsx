import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AdminLayout from '@/layouts/admin-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@components/ui/card';
import '@lib/i18n';

type Props = {
    subscription: {
        id: number;
        workspace_name: string | null;
        workspace_domain: string | null;
        stripe_id: string;
        stripe_status: string;
        stripe_price: string | null;
        quantity: number | null;
        trial_ends_at: string | null;
        ends_at: string | null;
        created_at: string | null;
        stripe_current_period_start: number | null;
        stripe_current_period_end: number | null;
        stripe_cancel_at_period_end: boolean;
    };
};

export default function AdminSubscriptionShow({ subscription }: Props) {
    const { t } = useTranslation();

    return (
        <AdminLayout>
            <Head title={t('admin.subscriptions.show.meta.title')} />
            <div className="space-y-4">
                <h1 className="text-2xl font-semibold">{t('admin.subscriptions.show.meta.title')}</h1>
                <Card>
                    <CardHeader>
                        <CardTitle>{subscription.workspace_name}</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-2 text-sm">
                        <p>{t('admin.subscriptions.show.fields.domain')}: {subscription.workspace_domain ?? t('admin.common.not_available')}</p>
                        <p>{t('admin.subscriptions.show.fields.status')}: {subscription.stripe_status}</p>
                        <p>{t('admin.subscriptions.show.fields.stripe_id')}: {subscription.stripe_id}</p>
                        <p>{t('admin.subscriptions.show.fields.stripe_price')}: {subscription.stripe_price ?? t('admin.common.not_available')}</p>
                        <p>{t('admin.subscriptions.show.fields.cancel_at_period_end')}: {subscription.stripe_cancel_at_period_end ? t('admin.common.yes') : t('admin.common.no')}</p>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
