import { Head, useForm } from '@inertiajs/react';
import { type SubmitEventHandler } from 'react';
import { useTranslation } from 'react-i18next';
import StoreRefundController from '@/actions/App/Http/Controllers/Admin/Invoices/StoreRefundController';
import AdminLayout from '@/layouts/admin-layout';
import { Button } from '@components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@components/ui/card';
import { Input } from '@components/ui/input';
import { Label } from '@components/ui/label';
import { formatCentsToEuro } from '@lib/utils';
import '@lib/i18n';

type Props = {
    invoice: {
        id: string;
        number: string | null;
        customer: string;
        workspace_name: string | null;
        amount_paid: number;
        amount_due: number;
        subtotal: number;
        total: number;
        currency: string;
        status: string;
        charge: string | null;
        hosted_invoice_url: string | null;
        invoice_pdf: string | null;
        created: number;
    };
};

export default function AdminInvoiceShow({ invoice }: Props) {
    const { t } = useTranslation();
    const form = useForm({
        amount: '',
        reason: 'requested_by_customer',
    });

    const submitRefund: SubmitEventHandler<HTMLFormElement> = (event) => {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            amount: data.amount === '' ? null : Number(data.amount),
        }));
        form.post(StoreRefundController.url(invoice.id));
    };

    return (
        <AdminLayout>
            <Head title={t('admin.invoices.show.meta.title')} />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">
                        {t('admin.invoices.show.meta.title')} #
                        {invoice.number ?? invoice.id}
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        {invoice.workspace_name ??
                            t('admin.common.not_available')}
                    </p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>
                            {t('admin.invoices.show.details.title')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-2 text-sm">
                        <p>
                            {t('admin.invoices.show.details.status')}:{' '}
                            {invoice.status}
                        </p>
                        <p>
                            {t('admin.invoices.show.details.amount_paid')}:{' '}
                            {formatCentsToEuro(invoice.amount_paid)}
                        </p>
                        <p>
                            {t('admin.invoices.show.details.amount_due')}:{' '}
                            {formatCentsToEuro(invoice.amount_due)}
                        </p>
                        {invoice.invoice_pdf !== null && (
                            <a
                                href={invoice.invoice_pdf}
                                target="_blank"
                                rel="noreferrer"
                                className="text-primary hover:underline"
                            >
                                {t('admin.invoices.show.details.download_pdf')}
                            </a>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>
                            {t('admin.invoices.show.refund.title')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submitRefund} className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="amount">
                                    {t('admin.invoices.show.refund.amount')}
                                </Label>
                                <Input
                                    id="amount"
                                    type="number"
                                    min={1}
                                    value={form.data.amount}
                                    onChange={(event) =>
                                        form.setData(
                                            'amount',
                                            event.target.value,
                                        )
                                    }
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="reason">
                                    {t('admin.invoices.show.refund.reason')}
                                </Label>
                                <Input
                                    id="reason"
                                    value={form.data.reason}
                                    onChange={(event) =>
                                        form.setData(
                                            'reason',
                                            event.target.value,
                                        )
                                    }
                                />
                            </div>
                            <Button type="submit" disabled={form.processing}>
                                {t('admin.invoices.show.refund.submit')}
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
