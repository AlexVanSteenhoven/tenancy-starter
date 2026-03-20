import { Head, Link } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { ArrowUpDown } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import AdminLayout from '@/layouts/admin-layout';
import { Button } from '@components/ui/button';
import { DataTable } from '@components/ui/data-table';
import { formatCentsToEuro } from '@lib/utils';
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
    plan_name: string | null;
    plan_price_monthly: number | null;
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
        {
            accessorKey: 'workspace_name',
            header: ({ column }) => (
                <Button
                    type="button"
                    variant="ghost"
                    className="-ml-3 h-8"
                    onClick={() =>
                        column.toggleSorting(column.getIsSorted() === 'asc')
                    }
                >
                    {t('admin.subscriptions.table.workspace')}
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
        },
        {
            accessorKey: 'stripe_status',
            header: ({ column }) => (
                <Button
                    type="button"
                    variant="ghost"
                    className="-ml-3 h-8"
                    onClick={() =>
                        column.toggleSorting(column.getIsSorted() === 'asc')
                    }
                >
                    {t('admin.subscriptions.table.status')}
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
        },
        {
            id: 'plan_price',
            accessorFn: (subscription) =>
                `${subscription.plan_name ?? ''} ${subscription.plan_price_monthly ?? ''}`,
            header: ({ column }) => (
                <Button
                    type="button"
                    variant="ghost"
                    className="-ml-3 h-8"
                    onClick={() =>
                        column.toggleSorting(column.getIsSorted() === 'asc')
                    }
                >
                    {t('admin.subscriptions.table.price')}
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
            cell: ({ row }) =>
                row.original.plan_name === null ||
                row.original.plan_price_monthly === null
                    ? t('admin.common.not_available')
                    : `${row.original.plan_name} (${formatCentsToEuro(row.original.plan_price_monthly)})`,
        },
        {
            accessorKey: 'created_at',
            header: ({ column }) => (
                <Button
                    type="button"
                    variant="ghost"
                    className="-ml-3 h-8"
                    onClick={() =>
                        column.toggleSorting(column.getIsSorted() === 'asc')
                    }
                >
                    {t('admin.subscriptions.table.created_at')}
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
        },
        {
            id: 'actions',
            header: t('admin.common.actions'),
            cell: ({ row }) => (
                <Link
                    href={`/admin/subscriptions/${row.original.id}`}
                    className="text-primary hover:underline"
                >
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
                    <h1 className="text-2xl font-semibold">
                        {t('admin.subscriptions.meta.title')}
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        {t('admin.subscriptions.meta.description')}
                    </p>
                </div>
                <DataTable columns={columns} data={subscriptions} />
            </div>
        </AdminLayout>
    );
}
