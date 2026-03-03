import { Head } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/layouts/app-layout';
import StatusBadge from '@components/status-badge';
import { Button } from '@components/ui/button';
import { useLabel } from '@hooks/use-label';
import type { BreadcrumbItem } from '@types';
import '@lib/i18n';

type ShowUserProps = {
    user: {
        id: string;
        name: string | null;
        email: string;
        status: string | null;
        role: string | null;
        created_at: string | null;
    };
};

export default function ShowUser({ user }: ShowUserProps) {
    const { t } = useTranslation();
    const { getLabel, translateRole } = useLabel();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('users.meta.title'),
            href: '/users',
        },
        {
            title: t('users.show-user.meta.title'),
            href: `/users/${user.id}`,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('users.show-user.meta.title')} />

            <div className="mx-auto mt-8 w-full max-w-2xl rounded bg-gray-100 p-4 dark:bg-secondary/50">
                <div className="mb-4 flex items-center justify-between">
                    <h1 className="text-xl font-semibold">
                        {t('users.show-user.title')}
                    </h1>
                    <Button asChild type="button" variant="secondary">
                        <Link href="/users">{t('users.show-user.back')}</Link>
                    </Button>
                </div>

                <dl className="grid gap-3 text-sm">
                    <div className="grid gap-1">
                        <dt className="text-muted-foreground">
                            {t('users.show-user.fields.id')}
                        </dt>
                        <dd>{user.id}</dd>
                    </div>

                    <div className="grid gap-1">
                        <dt className="text-muted-foreground">
                            {t('users.show-user.fields.name')}
                        </dt>
                        <dd>
                            {user.name && user.name.trim() !== ''
                                ? user.name
                                : t('users.columns.no-name')}
                        </dd>
                    </div>

                    <div className="grid gap-1">
                        <dt className="text-muted-foreground">
                            {t('users.show-user.fields.email')}
                        </dt>
                        <dd>{user.email}</dd>
                    </div>

                    <div className="grid gap-1">
                        <dt className="text-muted-foreground">
                            {t('users.show-user.fields.role')}
                        </dt>
                        <dd>{getLabel(user.role, translateRole)}</dd>
                    </div>

                    <div className="grid gap-1">
                        <dt className="text-muted-foreground">
                            {t('users.show-user.fields.status')}
                        </dt>
                        <dd><StatusBadge status={user.status} /></dd>
                    </div>
                </dl>
            </div>
        </AppLayout>
    );
}
