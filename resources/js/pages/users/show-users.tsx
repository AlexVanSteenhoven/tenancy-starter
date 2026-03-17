import { Head, router, useForm, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { ArrowUpDown, ChevronDown, MoreHorizontal } from 'lucide-react';
import { type SubmitEventHandler } from 'react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import '@lib/i18n';
import DeletePendingInvitationController from '@/actions/App/Http/Controllers/Users/DeletePendingInvitationController';
import DeleteUserController from '@/actions/App/Http/Controllers/Users/DeleteUserController';
import InviteUserController from '@/actions/App/Http/Controllers/Users/InviteUserController';
import ResendPendingInvitationController from '@/actions/App/Http/Controllers/Users/ResendPendingInvitationController';
import ShowUserController from '@/actions/App/Http/Controllers/Users/ShowUserController';
import ShowUsersController from '@/actions/App/Http/Controllers/Users/ShowUsersController';
import UpdateUserRoleController from '@/actions/App/Http/Controllers/Users/UpdateUserRoleController';
import UpdateUserStatusController from '@/actions/App/Http/Controllers/Users/UpdateUserStatusController';
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
    permissions: {
        canInviteUsers: boolean;
        canUpdateUsers: boolean;
        canDeleteUsers: boolean;
        canViewUsers: boolean;
    };
    filters: {
        status: string[];
        role: string[];
    };
};

const roles: Role[] = [Role.ADMIN, Role.MEMBER];
const editableStatuses: Status[] = [Status.ACTIVE, Status.INACTIVE];

const isRole = (value: string | null): value is Role =>
    value === Role.OWNER || value === Role.ADMIN || value === Role.MEMBER;

const isStatus = (value: string | null): value is Status =>
    value === Status.ACTIVE ||
    value === Status.DELETED ||
    value === Status.INACTIVE ||
    value === Status.PENDING ||
    value === Status.BLOCKED ||
    value === Status.SUSPENDED;

export default function ShowUsers({
    users,
    permissions,
    filters,
}: ShowUsersProps) {
    const { t } = useTranslation();
    const translateStatus = useEnumTranslation(statusTranslationMap);
    const { getLabel, translateRole } = useLabel();
    const { auth } = usePage<{ auth: Auth }>().props;
    const [isInviteDialogOpen, setIsInviteDialogOpen] = useState(false);
    const [editingUser, setEditingUser] = useState<UserTableRow | null>(null);
    const [editingRole, setEditingRole] = useState<Role>(Role.MEMBER);
    const [editingStatus, setEditingStatus] = useState<Status>(Status.ACTIVE);
    const [isSubmittingEdit, setIsSubmittingEdit] = useState(false);
    const [deletingRow, setDeletingRow] = useState<UserTableRow | null>(null);
    const [isDeletingRow, setIsDeletingRow] = useState(false);
    const [selectedStatusFilter, setSelectedStatusFilter] = useState('all');
    const [selectedRoleFilter, setSelectedRoleFilter] = useState('all');

    const inviteForm = useForm({
        email: '',
        role: Role.MEMBER,
    });

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('users.meta.title'),
            href: ShowUsersController.url(),
        },
    ];

    const handleInviteSubmit: SubmitEventHandler<HTMLFormElement> = (event) => {
        event.preventDefault();

        inviteForm.post(InviteUserController.url(), {
            preserveScroll: true,
            onSuccess: () => {
                inviteForm.reset();
                setIsInviteDialogOpen(false);
            },
        });
    };

    const roleOptions = useMemo(() => roles, []);
    const filteredUsers = useMemo(
        () =>
            users.filter((user) => {
                const matchesStatus =
                    selectedStatusFilter === 'all' ||
                    user.status === selectedStatusFilter;
                const matchesRole =
                    selectedRoleFilter === 'all' ||
                    user.role === selectedRoleFilter;

                return matchesStatus && matchesRole;
            }),
        [users, selectedRoleFilter, selectedStatusFilter],
    );

    const canEditRow = (row: UserTableRow): boolean =>
        permissions.canUpdateUsers &&
        row.type === 'user' &&
        auth.user?.id !== row.id &&
        row.status !== Status.PENDING;

    const canDeleteRow = (row: UserTableRow): boolean =>
        permissions.canDeleteUsers &&
        (row.type === 'invitation' ||
            (row.type === 'user' &&
                auth.user?.id !== row.id &&
                row.status !== Status.PENDING));

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
        if (!permissions.canUpdateUsers || !editingUser) {
            return;
        }

        setIsSubmittingEdit(true);

        const roleChanged =
            editingUser.role !== Role.OWNER && editingRole !== editingUser.role;
        const statusChanged = editingStatus !== editingUser.status;

        let successful = true;

        if (roleChanged) {
            successful = await patchUser(UpdateUserRoleController.url(editingUser.id), {
                role: editingRole,
            });
        }

        if (successful && statusChanged) {
            successful = await patchUser(UpdateUserStatusController.url(editingUser.id), {
                status: editingStatus,
            });
        }

        if (successful) {
            setEditingUser(null);
        }

        setIsSubmittingEdit(false);
    };

    const handleDeleteRow = (): void => {
        if (!permissions.canDeleteUsers || !deletingRow) {
            return;
        }

        setIsDeletingRow(true);

        const deleteUrl =
            deletingRow.type === 'invitation'
                ? DeletePendingInvitationController.url(deletingRow.id)
                : DeleteUserController.url(deletingRow.id);

        router.delete(deleteUrl, {
            preserveScroll: true,
            onSuccess: () => {
                setDeletingRow(null);
            },
            onFinish: () => {
                setIsDeletingRow(false);
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
                                !permissions.canUpdateUsers ||
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
                                        UpdateUserRoleController.url(
                                            row.original.id,
                                        ),
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
                            disabled={
                                row.original.type !== 'user' ||
                                !permissions.canViewUsers
                            }
                            onClick={() => {
                                if (
                                    row.original.type !== 'user' ||
                                    !permissions.canViewUsers
                                ) {
                                    return;
                                }

                                router.get(ShowUserController.url(row.original.id));
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
                            disabled={
                                row.original.type !== 'invitation' ||
                                !permissions.canInviteUsers
                            }
                            onClick={() => {
                                if (
                                    row.original.type !== 'invitation' ||
                                    !permissions.canInviteUsers
                                ) {
                                    return;
                                }

                                router.post(
                                    ResendPendingInvitationController.url(
                                        row.original.id,
                                    ),
                                    {},
                                    {
                                        preserveScroll: true,
                                    },
                                );
                            }}
                        >
                            {t('users.actions.resend-invitation')}
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            disabled={!canEditRow(row.original)}
                            onClick={() => openEditDialog(row.original)}
                        >
                            {t('users.actions.edit')}
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            disabled={!canDeleteRow(row.original)}
                            onClick={() => setDeletingRow(row.original)}
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
                    {permissions.canInviteUsers && (
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
                    data={filteredUsers}
                    searchPlaceholder={t('users.filters.search')}
                    toolbar={() => (
                        <div className="flex flex-wrap items-center gap-2">
                            <Select
                                value={selectedStatusFilter}
                                onValueChange={setSelectedStatusFilter}
                            >
                                <SelectTrigger className="w-[180px] bg-secondary/50">
                                    <SelectValue
                                        placeholder={t('users.filters.status')}
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">
                                        {t('users.filters.all-status')}
                                    </SelectItem>
                                    {filters.status.map((status) => (
                                        <SelectItem key={status} value={status}>
                                            {getLabel(status, translateStatus)}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <Select
                                value={selectedRoleFilter}
                                onValueChange={setSelectedRoleFilter}
                            >
                                <SelectTrigger className="w-[180px] bg-secondary/50">
                                    <SelectValue
                                        placeholder={t('users.filters.role')}
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">
                                        {t('users.filters.all-role')}
                                    </SelectItem>
                                    {filters.role.map((role) => (
                                        <SelectItem key={role} value={role}>
                                            {getLabel(role, translateRole)}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    )}
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
                open={deletingRow !== null}
                onOpenChange={(open) => {
                    if (!open && !isDeletingRow) {
                        setDeletingRow(null);
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            {deletingRow?.type === 'invitation'
                                ? t('users.delete.invitation-title')
                                : t('users.delete.title')}
                        </DialogTitle>
                        <DialogDescription>
                            {deletingRow?.type === 'invitation'
                                ? t('users.delete.invitation-description')
                                : t('users.delete.description')}
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="secondary"
                            disabled={isDeletingRow}
                            onClick={() => setDeletingRow(null)}
                        >
                            {t('users.actions.cancel')}
                        </Button>
                        <Button
                            type="button"
                            variant="destructive"
                            disabled={isDeletingRow}
                            onClick={handleDeleteRow}
                        >
                            {t('users.actions.delete')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
