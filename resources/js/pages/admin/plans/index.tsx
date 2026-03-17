import { Head, router, useForm } from '@inertiajs/react';
import { type SubmitEventHandler } from 'react';
import { useTranslation } from 'react-i18next';
import DeactivatePlanController from '@/actions/App/Http/Controllers/Admin/Plans/DeactivatePlanController';
import SyncPlansFromStripeController from '@/actions/App/Http/Controllers/Admin/Plans/SyncPlansFromStripeController';
import StorePlanController from '@/actions/App/Http/Controllers/Admin/Plans/StorePlanController';
import UpdatePlanController from '@/actions/App/Http/Controllers/Admin/Plans/UpdatePlanController';
import AdminLayout from '@/layouts/admin-layout';
import { Button } from '@components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@components/ui/card';
import { Input } from '@components/ui/input';
import { Label } from '@components/ui/label';
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
    const createForm = useForm({
        slug: '',
        name: '',
        description: '',
        price_monthly: 0,
        features: '',
        is_active: true,
    });

    const submit: SubmitEventHandler<HTMLFormElement> = (event) => {
        event.preventDefault();
        createForm.transform((data) => ({
            ...data,
            features: data.features
                .split('\n')
                .map((feature) => feature.trim())
                .filter((feature) => feature !== ''),
        }));
        createForm.post(StorePlanController.url());
    };

    return (
        <AdminLayout>
            <Head title={t('admin.plans.meta.title')} />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">{t('admin.plans.meta.title')}</h1>
                    <p className="text-sm text-muted-foreground">{t('admin.plans.meta.description')}</p>
                </div>
                <div>
                    <Button
                        variant="secondary"
                        onClick={() => {
                            router.post(SyncPlansFromStripeController.url());
                        }}
                    >
                        {t('admin.common.sync')}
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('admin.plans.create.title')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="slug">{t('admin.plans.fields.slug')}</Label>
                                <Input id="slug" value={createForm.data.slug} onChange={(event) => createForm.setData('slug', event.target.value)} />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="name">{t('admin.plans.fields.name')}</Label>
                                <Input id="name" value={createForm.data.name} onChange={(event) => createForm.setData('name', event.target.value)} />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="description">{t('admin.plans.fields.description')}</Label>
                                <Input id="description" value={createForm.data.description} onChange={(event) => createForm.setData('description', event.target.value)} />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="price">{t('admin.plans.fields.price_monthly')}</Label>
                                <Input id="price" type="number" min={0} value={createForm.data.price_monthly} onChange={(event) => createForm.setData('price_monthly', Number(event.target.value))} />
                            </div>
                            <div className="space-y-2 md:col-span-2">
                                <Label htmlFor="features">{t('admin.plans.fields.features')}</Label>
                                <Input id="features" value={createForm.data.features} onChange={(event) => createForm.setData('features', event.target.value)} />
                            </div>
                            <div className="md:col-span-2">
                                <Button type="submit" disabled={createForm.processing}>{t('admin.plans.create.submit')}</Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('admin.plans.table.title')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-3">
                            {plans.map((plan) => (
                                <div key={plan.id} className="flex flex-col gap-3 rounded-md border p-4 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <p className="font-medium">{plan.name} ({plan.slug})</p>
                                        <p className="text-xs text-muted-foreground">
                                            {t('admin.plans.table.price')}: {(plan.price_monthly / 100).toFixed(2)} USD
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            {t('admin.plans.table.stripe')}: {plan.stripe_product_id ?? '-'} / {plan.stripe_price_id ?? '-'}
                                        </p>
                                    </div>
                                    <div className="flex gap-2">
                                        <Button
                                            variant="outline"
                                            onClick={() => {
                                                router.patch(UpdatePlanController.url(plan.id), {
                                                    slug: plan.slug,
                                                    name: plan.name,
                                                    description: plan.description ?? '',
                                                    price_monthly: plan.price_monthly,
                                                    features: plan.features,
                                                    is_active: plan.is_active,
                                                });
                                            }}
                                        >
                                            {t('admin.common.update')}
                                        </Button>
                                        <Button
                                            variant="destructive"
                                            onClick={() => {
                                                router.delete(DeactivatePlanController.url(plan.id));
                                            }}
                                        >
                                            {t('admin.common.deactivate')}
                                        </Button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
