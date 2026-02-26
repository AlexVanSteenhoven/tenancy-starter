import { Head } from '@inertiajs/react';
import { Button } from '@components/ui/button';
import { DataTable } from '@components/ui/data-table';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@components/ui/dropdown-menu';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@components/ui/select';
import AppLayout from '@/layouts/app-layout';
import type { User } from '@types';
import type { BreadcrumbItem } from '@types';
import type { ColumnDef } from '@tanstack/react-table';
import { ArrowUpDown, MoreHorizontal } from 'lucide-react';
import { useTranslation } from 'react-i18next';

type ShowUsersProps = {
    users: User[];
};

export default function ShowUsers({ users }: ShowUsersProps) {
    const { t } = useTranslation();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('users.meta.title'),
            href: '/users',
        },
    ];

    const columns: ColumnDef<User>[] = [
        {
            accessorKey: 'name',
            header: ({ column }) => (
                <Button
                    type="button"
                    variant="ghost"
                    className="-ml-3 h-8"
                    onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                >
                    {t('users.columns.name')}
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
        },
        {
            accessorKey: 'email',
            header: ({ column }) => (
                <Button
                    type="button"
                    variant="ghost"
                    className="-ml-3 h-8"
                    onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                >
                    {t('users.columns.email')}
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
        },
        {
            accessorKey: 'email_verified_at',
            header: t('users.columns.status'),
            filterFn: (row, columnId, filterValue) => {
                const value = row.getValue<string | null>(columnId);

                if (filterValue === 'verified') {
                    return Boolean(value);
                }

                if (filterValue === 'unverified') {
                    return !value;
                }

                return true;
            },
            cell: ({ row }) =>
                row.original.email_verified_at ? t('users.filters.verified') : t('users.filters.unverified'),
        },
        {
            accessorKey: 'created_at',
            header: ({ column }) => (
                <Button
                    type="button"
                    variant="ghost"
                    className="-ml-3 h-8"
                    onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                >
                    {t('users.columns.created-at')}
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
            cell: ({ row }) =>
                new Intl.DateTimeFormat('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                }).format(new Date(row.original.created_at)),
        },
        {
            id: 'actions',
            header: t('users.actions.label'),
            cell: ({ row }) => (
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button type="button" variant="ghost" className="h-8 w-8 p-0">
                            <span className="sr-only">{t('users.actions.open-menu')}</span>
                            <MoreHorizontal className="h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem onClick={() => navigator.clipboard.writeText(String(row.original.id))}>
                            {t('users.actions.copy-id')}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('users.meta.title')} />

            <div className="flex flex-col gap-4 p-4">
                <DataTable
                    columns={columns}
                    data={users}
                    searchPlaceholder={t('users.filters.search')}
                    toolbar={(table) => (
                        <Select
                            value={(table.getColumn('email_verified_at')?.getFilterValue() as string) ?? 'all'}
                            onValueChange={(value) => {
                                table
                                    .getColumn('email_verified_at')
                                    ?.setFilterValue(value === 'all' ? undefined : value);
                            }}
                        >
                            <SelectTrigger className="w-[180px]">
                                <SelectValue placeholder={t('users.filters.status')} />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('users.filters.all')}</SelectItem>
                                <SelectItem value="verified">{t('users.filters.verified')}</SelectItem>
                                <SelectItem value="unverified">{t('users.filters.unverified')}</SelectItem>
                            </SelectContent>
                        </Select>
                    )}
                />
            </div>
        </AppLayout>
    );
}
