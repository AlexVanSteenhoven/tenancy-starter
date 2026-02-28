import { Head, router, useForm, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { ArrowUpDown, ChevronDown, MoreHorizontal } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/layouts/app-layout';
import { statusTranslationMap } from '@/types/enums';
import { Role, Status } from '@/types/enums';
import InputError from '@components/input-error';
import { Button } from '@components/ui/button';
import { DataTable } from '@components/ui/data-table';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@components/ui/dropdown-menu';
import { Input } from '@components/ui/input';
import { Label } from '@components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@components/ui/table';
import { useEnumTranslation } from '@hooks/use-enum-translation';
import { useLabel } from '@hooks/use-label';
import type { Auth, BreadcrumbItem } from '@types';

type UserTableRow = {
    id: string;
    name: string;
    email: string;
    created_at: string | null;
    status: Status | string | null;
    role: Role | string | null;
};

type PendingInvitationRow = {
    id: string;
    email: string;
    role: Role | string;
    invited_by: string | null;
    invited_at: string | null;
};

type ShowUsersProps = {
    users: UserTableRow[];
    pendingInvitations: PendingInvitationRow[];
};

const roles: Role[] = [Role.ADMIN, Role.MEMBER];

export default function ShowUsers({ users, pendingInvitations }: ShowUsersProps) {
    const { t } = useTranslation();
    const translateStatus = useEnumTranslation(statusTranslationMap);
    const { getLabel, translateRole } = useLabel();
    const { auth } = usePage<{ auth: Auth }>().props;
    const [isInviteDialogOpen, setIsInviteDialogOpen] = useState(false);

    const inviteForm = useForm({
        email: '',
        role: Role.MEMBER,
    });

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('users.meta.title'),
            href: '/users',
        },
    ];

    const handleInviteSubmit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        inviteForm.post('/users/invite', {
            preserveScroll: true,
            onSuccess: () => {
                inviteForm.reset();
                setIsInviteDialogOpen(false);
            },
        });
    };

    const roleOptions = useMemo(() => roles, []);

    const columns: ColumnDef<UserTableRow>[] = [
        {
            accessorKey: 'name',
            header: ({ column }) => (
                <Button
                    type="button"
                    variant="ghost"
                    className="-ml-3 h-8"
                    onClick={() =>
                        column.toggleSorting(column.getIsSorted() === 'asc')
                    }
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
                    onClick={() =>
                        column.toggleSorting(column.getIsSorted() === 'asc')
                    }
                >
                    {t('users.columns.email')}
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
        },
        {
            accessorKey: 'status',
            header: () => (
                <Button type="button" variant="ghost" className="-ml-4 h-8">
                    {t('users.columns.status')}
                </Button>
            ),
            cell: ({ row }) =>
                row.original.status
                    ? getLabel(row.original.status, translateStatus)
                    : t('users.columns.no-status'),
        },
        {
            accessorKey: 'role',
            header: ({ column }) => (
                <Button
                    type="button"
                    variant="ghost"
                    className="-ml-3 h-8"
                    onClick={() =>
                        column.toggleSorting(column.getIsSorted() === 'asc')
                    }
                >
                    {t('users.columns.role')}
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
            cell: ({ row }) => (
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button
                            disabled={
                                auth.user?.id === row.original.id ||
                                row.original.role === Role.OWNER
                            }
                            type="button"
                            variant="ghost"
                            className="h-8 w-24 justify-start border px-2"
                        >
                            {getLabel(row.original.role, translateRole)}
                            <ChevronDown className="ml-2 h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        {roles.map((roleOption) => (
                            <DropdownMenuItem
                                key={roleOption}
                                onClick={() => {
                                    router.patch(
                                        `/users/${row.original.id}/role`,
                                        {
                                            role: roleOption,
                                        },
                                        {
                                            preserveScroll: true,
                                        },
                                    );
                                }}
                            >
                                {getLabel(roleOption, translateRole)}
                            </DropdownMenuItem>
                        ))}
                    </DropdownMenuContent>
                </DropdownMenu>
            ),
        },
        {
            id: 'actions',
            header: t('users.actions.label'),
            cell: ({ row }) => (
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button
                            type="button"
                            variant="ghost"
                            className="h-8 w-8 p-0"
                        >
                            <span className="sr-only">
                                {t('users.actions.open-menu')}
                            </span>
                            <MoreHorizontal className="h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem
                            onClick={() =>
                                navigator.clipboard.writeText(
                                    String(row.original.id),
                                )
                            }
                        >
                            {t('users.actions.copy-id')}
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            disabled={auth.user?.id === row.original.id}
                            onClick={() => {
                                const nextStatus =
                                    row.original.status === Status.ACTIVE
                                        ? Status.INACTIVE
                                        : Status.ACTIVE;

                                router.patch(
                                    `/users/${row.original.id}/status`,
                                    {
                                        status: nextStatus,
                                    },
                                    {
                                        preserveScroll: true,
                                    },
                                );
                            }}
                        >
                            {row.original.status === Status.ACTIVE
                                ? t('users.actions.deactivate')
                                : t('users.actions.activate')}
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            disabled={auth.user?.id === row.original.id}
                            onClick={() =>
                                router.delete(`/users/${row.original.id}`, {
                                    preserveScroll: true,
                                })
                            }
                        >
                            {t('users.actions.remove')}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('users.meta.title')} />

            <div className="mx-auto mt-8 flex w-full max-w-7xl flex-col gap-4 rounded bg-gray-100 p-2 sm:p-4 dark:bg-secondary/50">
                <div className="flex justify-end">
                    <Dialog
                        open={isInviteDialogOpen}
                        onOpenChange={setIsInviteDialogOpen}
                    >
                        <DialogTrigger asChild>
                            <Button type="button">
                                {t('users.actions.invite')}
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>
                                    {t('users.invite.title')}
                                </DialogTitle>
                                <DialogDescription>
                                    {t('users.invite.description')}
                                </DialogDescription>
                            </DialogHeader>

                            <form
                                className="grid gap-4"
                                onSubmit={handleInviteSubmit}
                            >
                                <div className="grid gap-2">
                                    <Label htmlFor="invite-email">
                                        {t('users.invite.email.label')}
                                    </Label>
                                    <Input
                                        id="invite-email"
                                        type="email"
                                        value={inviteForm.data.email}
                                        onChange={(event) =>
                                            inviteForm.setData(
                                                'email',
                                                event.target.value,
                                            )
                                        }
                                        placeholder={t(
                                            'users.invite.email.placeholder',
                                        )}
                                    />
                                    <InputError message={inviteForm.errors.email} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="invite-role">
                                        {t('users.invite.role.label')}
                                    </Label>
                                    <Select
                                        value={inviteForm.data.role}
                                        onValueChange={(value) =>
                                            inviteForm.setData(
                                                'role',
                                                value as Role,
                                            )
                                        }
                                    >
                                        <SelectTrigger id="invite-role">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {roleOptions.map((roleOption) => (
                                                <SelectItem
                                                    key={roleOption}
                                                    value={roleOption}
                                                >
                                                    {getLabel(
                                                        roleOption,
                                                        translateRole,
                                                    )}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={inviteForm.errors.role} />
                                </div>

                                <DialogFooter>
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        onClick={() =>
                                            setIsInviteDialogOpen(false)
                                        }
                                    >
                                        {t('users.actions.cancel')}
                                    </Button>
                                    <Button
                                        type="submit"
                                        disabled={inviteForm.processing}
                                    >
                                        {t('users.invite.submit')}
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                <DataTable
                    columns={columns}
                    data={users}
                    searchPlaceholder={t('users.filters.search')}
                />

                <div className="rounded-md border bg-secondary p-4">
                    <h2 className="mb-4 text-lg font-semibold">
                        {t('users.pending-invitations.title')}
                    </h2>

                    {pendingInvitations.length === 0 ? (
                        <p className="text-muted-foreground text-sm">
                            {t('users.pending-invitations.empty')}
                        </p>
                    ) : (
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>
                                        {t(
                                            'users.pending-invitations.columns.email',
                                        )}
                                    </TableHead>
                                    <TableHead>
                                        {t(
                                            'users.pending-invitations.columns.role',
                                        )}
                                    </TableHead>
                                    <TableHead>
                                        {t(
                                            'users.pending-invitations.columns.invited-by',
                                        )}
                                    </TableHead>
                                    <TableHead>
                                        {t(
                                            'users.pending-invitations.columns.invited-at',
                                        )}
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {pendingInvitations.map((invitation) => (
                                    <TableRow key={invitation.id}>
                                        <TableCell>
                                            {invitation.email}
                                        </TableCell>
                                        <TableCell>
                                            {getLabel(
                                                invitation.role,
                                                translateRole,
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {invitation.invited_by ?? '-'}
                                        </TableCell>
                                        <TableCell>
                                            {invitation.invited_at
                                                ? new Date(
                                                      invitation.invited_at,
                                                  ).toLocaleDateString()
                                                : '-'}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
