import { Head, router, useForm, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { ArrowUpDown, ChevronDown, MoreHorizontal } from 'lucide-react';
import type React from 'react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import '@lib/i18n';
import AppLayout from '@/layouts/app-layout';
import { statusTranslationMap } from '@/types/enums';
import { Role, Status } from '@/types/enums';
import InputError from '@components/input-error';
import StatusBadge from '@components/status-badge';
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
import { useEnumTranslation } from '@hooks/use-enum-translation';
import { useLabel } from '@hooks/use-label';
import type { Auth, BreadcrumbItem } from '@types';

type UserTableRow = {
    id: string;
    name: string | null;
    email: string;
    created_at: string | null;
    status: Status | string | null;
    role: Role | string | null;
    type: 'user' | 'invitation';
};

type ShowUsersProps = {
    users: UserTableRow[];
    canInviteUsers: boolean;
};

const roles: Role[] = [Role.ADMIN, Role.MEMBER];
const editableStatuses: Status[] = [Status.ACTIVE, Status.INACTIVE];

const isRole = (value: string | null): value is Role =>
    value === Role.OWNER || value === Role.ADMIN || value === Role.MEMBER;

const isStatus = (value: string | null): value is Status =>
    value === Status.ACTIVE ||
    value === Status.INACTIVE ||
    value === Status.PENDING ||
    value === Status.BLOCKED ||
    value === Status.SUSPENDED ||
    value === Status.DELETED ||
    value === Status.ARCHIVED ||
    value === Status.VERIFIED ||
    value === Status.UNVERIFIED;

export default function ShowUsers({ users, canInviteUsers }: ShowUsersProps) {
    const { t } = useTranslation();
    const translateStatus = useEnumTranslation(statusTranslationMap);
    const { getLabel, translateRole } = useLabel();
    const { auth } = usePage<{ auth: Auth }>().props;
    const [isInviteDialogOpen, setIsInviteDialogOpen] = useState(false);
    const [editingUser, setEditingUser] = useState<UserTableRow | null>(null);
    const [editingRole, setEditingRole] = useState<Role>(Role.MEMBER);
    const [editingStatus, setEditingStatus] = useState<Status>(Status.ACTIVE);
    const [isSubmittingEdit, setIsSubmittingEdit] = useState(false);
    const [deletingUser, setDeletingUser] = useState<UserTableRow | null>(null);
    const [isDeletingUser, setIsDeletingUser] = useState(false);

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

    const handleInviteSubmit = (event: React.FormEvent<HTMLFormElement>) => {
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

    const canEditRow = (row: UserTableRow): boolean =>
        row.type === 'user' &&
        auth.user?.id !== row.id &&
        row.status !== Status.PENDING;

    const canDeleteRow = (row: UserTableRow): boolean =>
        row.type === 'user' &&
        auth.user?.id !== row.id &&
        row.status !== Status.PENDING;

    const openEditDialog = (row: UserTableRow): void => {
        setEditingUser(row);
        setEditingRole(isRole(row.role) ? row.role : Role.MEMBER);
        setEditingStatus(isStatus(row.status) ? row.status : Status.ACTIVE);
    };

    const patchUser = (
        url: string,
        payload: Record<string, string>,
    ): Promise<boolean> =>
        new Promise((resolve) => {
            router.patch(url, payload, {
                preserveScroll: true,
                onSuccess: () => {
                    resolve(true);
                },
                onError: () => {
                    resolve(false);
                },
                onCancel: () => {
                    resolve(false);
                },
            });
        });

    const handleEditSubmit = async (): Promise<void> => {
        if (!editingUser) {
            return;
        }

        setIsSubmittingEdit(true);

        const roleChanged =
            editingUser.role !== Role.OWNER && editingRole !== editingUser.role;
        const statusChanged = editingStatus !== editingUser.status;

        let successful = true;

        if (roleChanged) {
            successful = await patchUser(`/users/${editingUser.id}/role`, {
                role: editingRole,
            });
        }

        if (successful && statusChanged) {
            successful = await patchUser(`/users/${editingUser.id}/status`, {
                status: editingStatus,
            });
        }

        if (successful) {
            setEditingUser(null);
        }

        setIsSubmittingEdit(false);
    };

    const handleDeleteUser = (): void => {
        if (!deletingUser) {
            return;
        }

        setIsDeletingUser(true);

        router.delete(`/users/${deletingUser.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setDeletingUser(null);
            },
            onFinish: () => {
                setIsDeletingUser(false);
            },
        });
    };

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
            cell: ({ row }) =>
                row.original.name !== null &&
                row.original.name.trim() !== '' ? (
                    row.original.name
                ) : (
                    <span className="text-sm text-gray-400/90 italic">
                        {t('users.columns.no-name')}
                    </span>
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
            cell: ({ row }) => <StatusBadge status={row.original.status} />,
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
                                row.original.type === 'invitation' ||
                                auth.user?.id === row.original.id ||
                                row.original.role === Role.OWNER ||
                                row.original.status === Status.PENDING
                            }
                            type="button"
                            variant="ghost"
                            className="h-8 max-w-44 min-w-28 justify-between gap-2 border px-2"
                        >
                            <span className="min-w-0 flex-1 truncate text-left">
                                {getLabel(row.original.role, translateRole)}
                            </span>
                            <ChevronDown className="h-4 w-4 shrink-0" />
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
                            disabled={row.original.type !== 'user'}
                            onClick={() => {
                                if (row.original.type !== 'user') {
                                    return;
                                }

                                router.get(`/users/${row.original.id}`);
                            }}
                        >
                            {t('users.actions.show')}
                        </DropdownMenuItem>
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
                            disabled={!canEditRow(row.original)}
                            onClick={() => openEditDialog(row.original)}
                        >
                            {t('users.actions.edit')}
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            disabled={!canDeleteRow(row.original)}
                            onClick={() => setDeletingUser(row.original)}
                        >
                            {t('users.actions.delete')}
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
                    {canInviteUsers && (
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
                                        <InputError
                                            message={inviteForm.errors.email}
                                        />
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
                                                {roleOptions.map(
                                                    (roleOption) => (
                                                        <SelectItem
                                                            key={roleOption}
                                                            value={roleOption}
                                                        >
                                                            {getLabel(
                                                                roleOption,
                                                                translateRole,
                                                            )}
                                                        </SelectItem>
                                                    ),
                                                )}
                                            </SelectContent>
                                        </Select>
                                        <InputError
                                            message={inviteForm.errors.role}
                                        />
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
                    )}
                </div>

                <DataTable
                    columns={columns}
                    data={users}
                    searchPlaceholder={t('users.filters.search')}
                />
            </div>

            <Dialog
                open={editingUser !== null}
                onOpenChange={(open) => {
                    if (!open && !isSubmittingEdit) {
                        setEditingUser(null);
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('users.edit.title')}</DialogTitle>
                        <DialogDescription>
                            {t('users.edit.description')}
                        </DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="edit-user-role">
                                {t('users.edit.role.label')}
                            </Label>
                            <Select
                                value={editingRole}
                                onValueChange={(value) =>
                                    setEditingRole(value as Role)
                                }
                                disabled={editingUser?.role === Role.OWNER}
                            >
                                <SelectTrigger id="edit-user-role">
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
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="edit-user-status">
                                {t('users.edit.status.label')}
                            </Label>
                            <Select
                                value={editingStatus}
                                onValueChange={(value) =>
                                    setEditingStatus(value as Status)
                                }
                            >
                                <SelectTrigger id="edit-user-status">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {editableStatuses.map((statusOption) => (
                                        <SelectItem
                                            key={statusOption}
                                            value={statusOption}
                                        >
                                            {getLabel(
                                                statusOption,
                                                translateStatus,
                                            )}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="secondary"
                            disabled={isSubmittingEdit}
                            onClick={() => setEditingUser(null)}
                        >
                            {t('users.actions.cancel')}
                        </Button>
                        <Button
                            type="button"
                            disabled={isSubmittingEdit}
                            onClick={() => {
                                void handleEditSubmit();
                            }}
                        >
                            {t('users.actions.save')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Dialog
                open={deletingUser !== null}
                onOpenChange={(open) => {
                    if (!open && !isDeletingUser) {
                        setDeletingUser(null);
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('users.delete.title')}</DialogTitle>
                        <DialogDescription>
                            {t('users.delete.description')}
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="secondary"
                            disabled={isDeletingUser}
                            onClick={() => setDeletingUser(null)}
                        >
                            {t('users.actions.cancel')}
                        </Button>
                        <Button
                            type="button"
                            variant="destructive"
                            disabled={isDeletingUser}
                            onClick={handleDeleteUser}
                        >
                            {t('users.actions.delete')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
