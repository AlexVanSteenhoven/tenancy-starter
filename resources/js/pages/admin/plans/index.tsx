import { Badge } from '@components/ui/badge';
import { Button } from '@components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@components/ui/card';
import { DataTable } from '@components/ui/data-table';
import { Head, router } from '@inertiajs/react';
import { formatCentsToEuro } from '@lib/utils';
import type { ColumnDef } from '@tanstack/react-table';
import { ArrowUpDown } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import SyncPlansFromStripeController from '@/actions/App/Http/Controllers/Admin/Plans/SyncPlansFromStripeController';
import AdminLayout from '@/layouts/admin-layout';
import '@lib/i18n';

type PlanRow = {
    id: number;
    slug: string;
    name: string;
    description: string | null;
    price_monthly: number;
    stripe_product_id: string | null;
    stripe_price_id: string | null;
    features: string[];
    is_active: boolean;
};

type Props = {
    plans: PlanRow[];
};

export default function AdminPlansIndex({ plans }: Props) {
    const { t } = useTranslation();
    const columns: ColumnDef<PlanRow>[] = [
        {
            accessorKey: 'name',
            header: ({ column }) => (
                <Button
                    type="button"
                    variant="ghost"
                    className="-ml-3 h-8"
                    onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                >
                    {t('admin.plans.table.name')}
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
        },
        {
            accessorKey: 'slug',
            header: ({ column }) => (
                <Button
                    type="button"
                    variant="ghost"
                    className="-ml-3 h-8"
                    onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                >
                    {t('admin.plans.table.slug')}
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
        },
        {
            accessorKey: 'price_monthly',
            header: ({ column }) => (
                <Button
                    type="button"
                    variant="ghost"
                    className="-ml-3 h-8"
                    onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                >
                    {t('admin.plans.table.price')}
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
            cell: ({ row }) => formatCentsToEuro(row.original.price_monthly),
        },
        {
            accessorKey: 'is_active',
            header: ({ column }) => (
                <Button
                    type="button"
                    variant="ghost"
                    className="-ml-3 h-8"
                    onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                >
                    {t('admin.plans.table.status')}
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
            cell: ({ row }) => (
                <Badge
                    variant="outline"
                    className={
                        row.original.is_active
                            ? 'rounded-full border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300'
                            : 'rounded-full border-slate-300 bg-slate-100 text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'
                    }
                >
                    {row.original.is_active ? t('admin.plans.status.active') : t('admin.plans.status.inactive')}
                </Badge>
            ),
        },
        {
            id: 'stripe',
            accessorFn: (plan) => `${plan.stripe_product_id ?? ''} ${plan.stripe_price_id ?? ''}`,
            header: ({ column }) => (
                <Button
                    type="button"
                    variant="ghost"
                    className="-ml-3 h-8"
                    onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                >
                    {t('admin.plans.table.stripe')}
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
            cell: ({ row }) => `${row.original.stripe_product_id ?? '-'} / ${row.original.stripe_price_id ?? '-'}`,
        },
    ];

    return (
        <AdminLayout>
            <Head title={t('admin.plans.meta.title')} />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">{t('admin.plans.meta.title')}</h1>
                    <p className="text-sm text-muted-foreground">{t('admin.plans.meta.description')}</p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('admin.plans.stripe_instructions.title')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-sm text-muted-foreground">{t('admin.plans.stripe_instructions.description')}</p>
                        <ul className="mt-4 list-disc space-y-2 pl-5 text-sm text-muted-foreground">
                            <li>{t('admin.plans.stripe_instructions.step1')}</li>
                            <li>{t('admin.plans.stripe_instructions.step2')}</li>
                            <li>{t('admin.plans.stripe_instructions.step3')}</li>
                            <li>{t('admin.plans.stripe_instructions.step4')}</li>
                            <li>{t('admin.plans.stripe_instructions.step5')}</li>
                        </ul>
                    </CardContent>
                </Card>

                <div>
                    <Button
                        variant="secondary"
                        onClick={() => {
                            router.post(SyncPlansFromStripeController.url());
                        }}
                    >
                        {t('admin.plans.actions.fetch')}
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('admin.plans.table.title')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <DataTable columns={columns} data={plans} />
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
