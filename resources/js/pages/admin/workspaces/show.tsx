import { Head, router, useForm } from '@inertiajs/react';
import { type SubmitEventHandler } from 'react';
import { useTranslation } from 'react-i18next';
import CancelWorkspaceSubscriptionController from '@/actions/App/Http/Controllers/Admin/Workspaces/CancelWorkspaceSubscriptionController';
import UpdateWorkspaceSubscriptionController from '@/actions/App/Http/Controllers/Admin/Workspaces/UpdateWorkspaceSubscriptionController';
import AdminLayout from '@/layouts/admin-layout';
import { Button } from '@components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@components/ui/card';
import { Label } from '@components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@components/ui/select';
import '@lib/i18n';

type Props = {
    workspace: {
        id: string;
        name: string;
        plan: string | null;
        domain: string | null;
        stripe_id: string | null;
        subscription: {
            id: number | null;
            stripe_id: string | null;
            status: string | null;
            stripe_price: string | null;
            ends_at: string | null;
        };
    };
    availablePlans: Array<{
        slug: string;
        name: string;
        price_monthly: number;
    }>;
};

export default function AdminWorkspaceShow({
    workspace,
    availablePlans,
}: Props) {
    const { t } = useTranslation();
    const form = useForm({
        plan: workspace.plan ?? availablePlans[0]?.slug ?? '',
    });

    const submit: SubmitEventHandler<HTMLFormElement> = (event) => {
        event.preventDefault();
        form.patch(UpdateWorkspaceSubscriptionController.url(workspace.id));
    };

    const cancelSubscription = () => {
        router.delete(CancelWorkspaceSubscriptionController.url(workspace.id));
    };

    return (
        <AdminLayout>
            <Head
                title={t('admin.workspaces.show.meta.title', {
                    workspace: workspace.name,
                })}
            />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">{workspace.name}</h1>
                    <p className="text-sm text-muted-foreground">
                        {workspace.domain}
                    </p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>
                            {t('admin.workspaces.show.subscription.title')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <p className="text-sm">
                            {t('admin.workspaces.show.subscription.status')}:{' '}
                            {workspace.subscription.status ??
                                t('admin.common.not_available')}
                        </p>
                        <p className="text-sm">
                            {t('admin.workspaces.show.subscription.stripe_id')}:{' '}
                            {workspace.subscription.stripe_id ??
                                t('admin.common.not_available')}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>
                            {t(
                                'admin.workspaces.show.subscription.change_plan',
                            )}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="plan">
                                    {t(
                                        'admin.workspaces.show.subscription.plan_label',
                                    )}
                                </Label>
                                <Select
                                    value={form.data.plan}
                                    onValueChange={(value) =>
                                        form.setData('plan', value)
                                    }
                                >
                                    <SelectTrigger id="plan">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {availablePlans.map((plan) => (
                                            <SelectItem
                                                key={plan.slug}
                                                value={plan.slug}
                                            >
                                                {plan.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="flex flex-col gap-2 sm:flex-row">
                                <Button
                                    type="submit"
                                    disabled={form.processing}
                                >
                                    {t(
                                        'admin.workspaces.show.subscription.update_button',
                                    )}
                                </Button>
                                <Button
                                    type="button"
                                    variant="destructive"
                                    onClick={cancelSubscription}
                                >
                                    {t(
                                        'admin.workspaces.show.subscription.cancel_button',
                                    )}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
