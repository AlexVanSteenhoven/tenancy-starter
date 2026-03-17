import { Head, Link } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { useTranslation } from 'react-i18next';
import AdminLayout from '@/layouts/admin-layout';
import { DataTable } from '@components/ui/data-table';
import '@lib/i18n';

type InvoiceRow = {
    id: string;
    number: string | null;
    customer: string;
    workspace_name: string | null;
    amount_paid: number;
    amount_due: number;
    currency: string;
    status: string;
    hosted_invoice_url: string | null;
    invoice_pdf: string | null;
    created: number;
};

type Props = {
    invoices: InvoiceRow[];
};

export default function AdminInvoicesIndex({ invoices }: Props) {
    const { t } = useTranslation();

    const columns: ColumnDef<InvoiceRow>[] = [
        { accessorKey: 'number', header: t('admin.invoices.table.number') },
        { accessorKey: 'workspace_name', header: t('admin.invoices.table.workspace') },
        { accessorKey: 'status', header: t('admin.invoices.table.status') },
        { accessorKey: 'amount_paid', header: t('admin.invoices.table.amount_paid') },
        {
            id: 'actions',
            header: t('admin.common.actions'),
            cell: ({ row }) => (
                <Link href={`/admin/invoices/${row.original.id}`} className="text-primary hover:underline">
                    {t('admin.common.view')}
                </Link>
            ),
        },
    ];

    return (
        <AdminLayout>
            <Head title={t('admin.invoices.meta.title')} />
            <div className="space-y-4">
                <div>
                    <h1 className="text-2xl font-semibold">{t('admin.invoices.meta.title')}</h1>
                    <p className="text-sm text-muted-foreground">{t('admin.invoices.meta.description')}</p>
                </div>
                <DataTable columns={columns} data={invoices} />
            </div>
        </AdminLayout>
    );
}
