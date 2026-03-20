import { Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AdminLayout from '@/layouts/admin-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@components/ui/card';
import '@lib/i18n';

type Props = {
    stats: {
        totalWorkspaces: number;
        totalActiveSubscriptions: number;
        mrrInCents: number;
    };
    recentWorkspaces: Array<{
        id: string;
        name: string;
        plan: string | null;
        domain: string | null;
        created_at: string | null;
    }>;
};

export default function AdminDashboard({ stats, recentWorkspaces }: Props) {
    const { t } = useTranslation();

    const mrrFormatted = new Intl.NumberFormat(undefined, {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
    }).format(stats.mrrInCents / 100);

    return (
        <AdminLayout>
            <Head title={t('admin.dashboard.meta.title')} />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">
                        {t('admin.dashboard.meta.title')}
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        {t('admin.dashboard.meta.description')}
                    </p>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader>
                            <CardTitle>
                                {t('admin.dashboard.stats.total_workspaces')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="text-3xl font-semibold">
                            {stats.totalWorkspaces}
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>
                                {t(
                                    'admin.dashboard.stats.active_subscriptions',
                                )}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="text-3xl font-semibold">
                            {stats.totalActiveSubscriptions}
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>
                                {t('admin.dashboard.stats.mrr')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="text-3xl font-semibold">
                            {mrrFormatted}
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>
                            {t('admin.dashboard.recent.title')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-2">
                            {recentWorkspaces.map((workspace) => (
                                <div
                                    key={workspace.id}
                                    className="flex items-center justify-between rounded-md border p-3"
                                >
                                    <div>
                                        <p className="font-medium">
                                            {workspace.name}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            {workspace.domain ??
                                                t('admin.common.not_available')}
                                        </p>
                                    </div>
                                    <Link
                                        className="text-sm text-primary hover:underline"
                                        href={`/admin/workspaces/${workspace.id}`}
                                    >
                                        {t('admin.dashboard.recent.open')}
                                    </Link>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
