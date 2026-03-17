import { Head, Link } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { useTranslation } from 'react-i18next';
import AdminLayout from '@/layouts/admin-layout';
import { DataTable } from '@components/ui/data-table';
import '@lib/i18n';

type WorkspaceRow = {
    id: string;
    name: string;
    domain: string | null;
    plan: string | null;
    plan_name: string | null;
    subscription_status: string | null;
    stripe_id: string | null;
    created_at: string | null;
};

type Props = {
    workspaces: WorkspaceRow[];
};

export default function AdminWorkspacesIndex({ workspaces }: Props) {
    const { t } = useTranslation();

    const columns: ColumnDef<WorkspaceRow>[] = [
        { accessorKey: 'name', header: t('admin.workspaces.table.name') },
        { accessorKey: 'domain', header: t('admin.workspaces.table.domain') },
        { accessorKey: 'plan_name', header: t('admin.workspaces.table.plan') },
        { accessorKey: 'subscription_status', header: t('admin.workspaces.table.subscription_status') },
        {
            id: 'actions',
            header: t('admin.common.actions'),
            cell: ({ row }) => (
                <Link href={`/admin/workspaces/${row.original.id}`} className="text-primary hover:underline">
                    {t('admin.common.view')}
                </Link>
            ),
        },
    ];

    return (
        <AdminLayout>
            <Head title={t('admin.workspaces.meta.title')} />

            <div className="space-y-4">
                <div>
                    <h1 className="text-2xl font-semibold">{t('admin.workspaces.meta.title')}</h1>
                    <p className="text-sm text-muted-foreground">{t('admin.workspaces.meta.description')}</p>
                </div>

                <DataTable
                    columns={columns}
                    data={workspaces}
                    searchPlaceholder={t('admin.workspaces.search_placeholder')}
                />
            </div>
        </AdminLayout>
    );
}
