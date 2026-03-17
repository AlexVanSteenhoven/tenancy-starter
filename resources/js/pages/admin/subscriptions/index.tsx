import { Head, Link } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { useTranslation } from 'react-i18next';
import AdminLayout from '@/layouts/admin-layout';
import { DataTable } from '@components/ui/data-table';
import '@lib/i18n';

type SubscriptionRow = {
    id: number;
    workspace_id: string;
    workspace_name: string | null;
    workspace_domain: string | null;
    type: string;
    stripe_id: string;
    stripe_status: string;
    stripe_price: string | null;
    quantity: number | null;
    trial_ends_at: string | null;
    ends_at: string | null;
    created_at: string | null;
};

type Props = {
    subscriptions: SubscriptionRow[];
};

export default function AdminSubscriptionsIndex({ subscriptions }: Props) {
    const { t } = useTranslation();

    const columns: ColumnDef<SubscriptionRow>[] = [
        { accessorKey: 'workspace_name', header: t('admin.subscriptions.table.workspace') },
        { accessorKey: 'stripe_status', header: t('admin.subscriptions.table.status') },
        { accessorKey: 'stripe_price', header: t('admin.subscriptions.table.price') },
        { accessorKey: 'created_at', header: t('admin.subscriptions.table.created_at') },
        {
            id: 'actions',
            header: t('admin.common.actions'),
            cell: ({ row }) => (
                <Link href={`/admin/subscriptions/${row.original.id}`} className="text-primary hover:underline">
                    {t('admin.common.view')}
                </Link>
            ),
        },
    ];

    return (
        <AdminLayout>
            <Head title={t('admin.subscriptions.meta.title')} />
            <div className="space-y-4">
                <div>
                    <h1 className="text-2xl font-semibold">{t('admin.subscriptions.meta.title')}</h1>
                    <p className="text-sm text-muted-foreground">{t('admin.subscriptions.meta.description')}</p>
                </div>
                <DataTable columns={columns} data={subscriptions} />
            </div>
        </AdminLayout>
    );
}
