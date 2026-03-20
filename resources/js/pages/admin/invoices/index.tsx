import { Head, Link } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { ArrowUpDown } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import AdminLayout from '@/layouts/admin-layout';
import { Button } from '@components/ui/button';
import { DataTable } from '@components/ui/data-table';
import { formatCentsToEuro } from '@lib/utils';
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
        {
            accessorKey: 'number',
            header: ({ column }) => (
                <Button
                    type="button"
                    variant="ghost"
                    className="-ml-3 h-8"
                    onClick={() =>
                        column.toggleSorting(column.getIsSorted() === 'asc')
                    }
                >
                    {t('admin.invoices.table.number')}
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
        },
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
                    {t('admin.invoices.table.workspace')}
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
        },
        {
            accessorKey: 'status',
            header: ({ column }) => (
                <Button
                    type="button"
                    variant="ghost"
                    className="-ml-3 h-8"
                    onClick={() =>
                        column.toggleSorting(column.getIsSorted() === 'asc')
                    }
                >
                    {t('admin.invoices.table.status')}
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
        },
        {
            accessorKey: 'amount_paid',
            header: ({ column }) => (
                <Button
                    type="button"
                    variant="ghost"
                    className="-ml-3 h-8"
                    onClick={() =>
                        column.toggleSorting(column.getIsSorted() === 'asc')
                    }
                >
                    {t('admin.invoices.table.amount_paid')}
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
            cell: ({ row }) => formatCentsToEuro(row.original.amount_paid),
        },
        {
            id: 'actions',
            header: t('admin.common.actions'),
            cell: ({ row }) => (
                <Link
                    href={`/admin/invoices/${row.original.id}`}
                    className="text-primary hover:underline"
                >
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
                    <h1 className="text-2xl font-semibold">
                        {t('admin.invoices.meta.title')}
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        {t('admin.invoices.meta.description')}
                    </p>
                </div>
                <DataTable columns={columns} data={invoices} />
            </div>
        </AdminLayout>
    );
}
