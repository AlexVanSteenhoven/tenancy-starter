import {
    CardCvcElement,
    CardExpiryElement,
    CardNumberElement,
    Elements,
    useElements,
    useStripe,
} from '@stripe/react-stripe-js';
import { loadStripe } from '@stripe/stripe-js';
import { Head, useForm } from '@inertiajs/react';
import type { SubmitEventHandler } from 'react';
import { useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { store } from '@/routes/onboarding/billing';
import AuthCardLayout from '@/layouts/auth/auth-card-layout';
import InputError from '@components/input-error';
import { Badge } from '@components/ui/badge';
import { Button } from '@components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@components/ui/card';
import { Input } from '@components/ui/input';
import { Label } from '@components/ui/label';
import { Spinner } from '@components/ui/spinner';
import '@lib/i18n';

type Workspace = {
    name: string;
    domain: string;
};

type BillingPlan = {
    slug: string;
    name: string;
    description: string | null;
    price_monthly: number;
    features: string[];
};

type Props = {
    workspace: Workspace;
    plans: BillingPlan[];
    stripeKey: string | null;
};

const stripeElementClassName =
    'w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground focus-within:border-ring';

function BillingForm({
    plans,
    workspace,
    stripeEnabled,
}: Omit<Props, 'stripeKey'> & { stripeEnabled: boolean }) {
    const { t } = useTranslation();
    const stripe = useStripe();
    const elements = useElements();
    const defaultPlan = plans[0]?.slug ?? 'free';
    const [selectedPlan, setSelectedPlan] = useState<string>(defaultPlan);
    const [currentStep, setCurrentStep] = useState<'plan' | 'payment'>('plan');
    const [seatQuantity, setSeatQuantity] = useState<number>(1);
    const [selectedPaymentMethod, setSelectedPaymentMethod] = useState<
        'card' | 'ideal_wero' | 'sepa' | 'paypal'
    >('card');
    const [stripeError, setStripeError] = useState<string>('');
    const [isDarkMode, setIsDarkMode] = useState<boolean>(false);
    const form = useForm({
        workspace: '',
        plan: defaultPlan,
        seats: 1,
        payment_method: '',
        billing_name: '',
        billing_email: '',
        billing_country: '',
        billing_vat: '',
    });

    const selectedPlanData =
        plans.find((plan) => plan.slug === selectedPlan) ?? plans[0];
    const requiresPayment = (selectedPlanData?.price_monthly ?? 0) > 0;
    const subtotalAmount = (selectedPlanData?.price_monthly ?? 0) * seatQuantity;
    const stripeElementOptions = useMemo(
        () => ({
            style: {
                base: {
                    color: isDarkMode ? '#f8fafc' : '#0f172a',
                    fontSize: '14px',
                    '::placeholder': {
                        color: isDarkMode ? '#94a3b8' : '#64748b',
                    },
                },
                invalid: {
                    color: '#ef4444',
                },
            },
        }),
        [isDarkMode],
    );

    useEffect(() => {
        const update = () => {
            setIsDarkMode(document.documentElement.classList.contains('dark'));
        };

        update();

        const observer = new MutationObserver(update);
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });

        return () => observer.disconnect();
    }, []);

    const formattedAmount = (amountInCents: number): string => {
        return new Intl.NumberFormat(undefined, {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 0,
        }).format(amountInCents / 100);
    };

    const goToNextStep = () => {
        if (!requiresPayment) {
            void submitForm();

            return;
        }

        setCurrentStep('payment');
    };

    const submitForm = async () => {
        setStripeError('');

        if (!requiresPayment) {
            form.transform((data) => ({
                ...data,
                plan: selectedPlan,
                seats: seatQuantity,
                payment_method: '',
            }));

            form.post(store.url(), {
                onFinish: () => {
                    form.transform((data) => data);
                },
            });

            return;
        }

        if (!stripeEnabled || stripe === null || elements === null) {
            setStripeError(t('onboarding.billing.validation.stripe_not_ready'));

            return;
        }

        if (selectedPaymentMethod !== 'card') {
            setStripeError(
                t('onboarding.billing.validation.payment_method_unavailable'),
            );

            return;
        }

        const cardNumberElement = elements.getElement(CardNumberElement);

        if (cardNumberElement === null) {
            setStripeError(t('onboarding.billing.validation.card_required'));

            return;
        }

        const { error, paymentMethod } = await stripe.createPaymentMethod({
            type: 'card',
            card: cardNumberElement,
        });

        if (error !== undefined) {
            setStripeError(
                error.message ??
                    t('onboarding.billing.validation.payment_failed'),
            );

            return;
        }

        form.transform((data) => ({
            ...data,
            plan: selectedPlan,
            seats: seatQuantity,
            payment_method: paymentMethod.id,
        }));

        form.post(store.url(), {
            onFinish: () => {
                form.transform((data) => data);
            },
        });
    };

    const submit: SubmitEventHandler<HTMLFormElement> = async (event) => {
        event.preventDefault();
        void submitForm();
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <input type="hidden" name="plan" value={selectedPlan} />
            <input type="hidden" name="seats" value={String(seatQuantity)} />
            <input
                type="hidden"
                name="payment_method"
                value={form.data.payment_method}
            />

            <div className="space-y-3">
                <div className="flex items-center justify-center gap-2 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                    <span
                        className={
                            currentStep === 'plan'
                                ? 'font-bold text-primary'
                                : ''
                        }
                    >
                        {t('onboarding.billing.steps.plan')}
                    </span>
                    <span>•</span>
                    <span
                        className={
                            currentStep === 'payment'
                                ? 'font-bold text-primary'
                                : ''
                        }
                    >
                        {t('onboarding.billing.steps.payment')}
                    </span>
                </div>
            </div>

            {currentStep === 'plan' && (
                <div className="flex flex-col gap-4 md:flex-row">
                    {plans.map((plan) => {
                        const isSelected = selectedPlan === plan.slug;
                        const isPaidPlan = plan.price_monthly > 0;

                        return (
                            <button
                                key={plan.slug}
                                type="button"
                                onClick={() => {
                                    setSelectedPlan(plan.slug);
                                    setStripeError('');
                                    form.setData('plan', plan.slug);
                                }}
                                className="flex-1 text-left"
                            >
                                <Card
                                    className={[
                                        'h-full transition-all',
                                        isSelected
                                            ? 'border-primary ring-2 ring-primary/15'
                                            : 'hover:border-ring/70',
                                    ].join(' ')}
                                >
                                    <CardHeader className="space-y-3">
                                        <div className="flex flex-wrap items-center justify-between gap-2">
                                            <CardTitle>
                                                {t(
                                                    `onboarding.billing.plans.${plan.slug}.name`,
                                                )}
                                            </CardTitle>
                                            {isSelected && (
                                                <Badge>
                                                    {t(
                                                        'onboarding.billing.selected',
                                                    )}
                                                </Badge>
                                            )}
                                        </div>
                                        <CardDescription>
                                            {t(
                                                `onboarding.billing.plans.${plan.slug}.description`,
                                            )}
                                        </CardDescription>
                                        <p className="text-3xl leading-none font-semibold">
                                            {isPaidPlan
                                                ? t(
                                                      'onboarding.billing.price.monthly',
                                                      {
                                                          amount: formattedAmount(
                                                              plan.price_monthly,
                                                          ),
                                                      },
                                                  )
                                                : t(
                                                      'onboarding.billing.price.free',
                                                  )}
                                        </p>
                                    </CardHeader>
                                    <CardContent>
                                        <ul className="space-y-2 text-sm text-muted-foreground">
                                            {(plan.features ?? []).map(
                                                (feature) => (
                                                    <li
                                                        key={feature}
                                                        className="leading-relaxed"
                                                    >
                                                        {t(feature)}
                                                    </li>
                                                ),
                                            )}
                                        </ul>
                                    </CardContent>
                                </Card>
                            </button>
                        );
                    })}
                </div>
            )}

            {currentStep === 'payment' && requiresPayment && (
                <div className="grid gap-8 lg:grid-cols-[1.35fr_1fr]">
                    <div className="space-y-3">
                        <p className="text-sm font-medium text-foreground">
                            {t('onboarding.billing.methods.title')}
                        </p>

                        <div className="overflow-hidden rounded-md border border-input bg-card">
                            <button
                                type="button"
                                onClick={() => setSelectedPaymentMethod('card')}
                                className={[
                                    'w-full border-b border-input px-4 py-3 text-left text-sm transition-all',
                                    selectedPaymentMethod === 'card'
                                        ? 'border-l-2 border-l-primary bg-primary/10 font-medium text-primary'
                                        : 'text-muted-foreground hover:bg-accent/20 hover:text-foreground',
                                ].join(' ')}
                            >
                                {t('onboarding.billing.methods.card')}
                            </button>

                            {selectedPaymentMethod === 'card' && (
                                <div className="space-y-4 border-b border-input px-4 py-4">
                                    <div className="grid gap-3 md:grid-cols-[2fr_1fr_1fr]">
                                        <div className="space-y-2">
                                            <Label htmlFor="card-number">
                                                {t(
                                                    'onboarding.billing.card.number',
                                                )}
                                            </Label>
                                            <div
                                                className={
                                                    stripeElementClassName
                                                }
                                            >
                                                <CardNumberElement
                                                    id="card-number"
                                                    options={
                                                        stripeElementOptions
                                                    }
                                                />
                                            </div>
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="card-expiry">
                                                {t(
                                                    'onboarding.billing.card.expiry',
                                                )}
                                            </Label>
                                            <div
                                                className={
                                                    stripeElementClassName
                                                }
                                            >
                                                <CardExpiryElement
                                                    id="card-expiry"
                                                    options={
                                                        stripeElementOptions
                                                    }
                                                />
                                            </div>
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="card-cvc">
                                                {t(
                                                    'onboarding.billing.card.cvc',
                                                )}
                                            </Label>
                                            <div
                                                className={
                                                    stripeElementClassName
                                                }
                                            >
                                                <CardCvcElement
                                                    id="card-cvc"
                                                    options={
                                                        stripeElementOptions
                                                    }
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="billing-country">
                                            {t(
                                                'onboarding.billing.biller.country',
                                            )}
                                        </Label>
                                        <Input
                                            id="billing-country"
                                            value={form.data.billing_country}
                                            className="bg-background"
                                            onChange={(event) =>
                                                form.setData(
                                                    'billing_country',
                                                    event.target.value,
                                                )
                                            }
                                            placeholder={t(
                                                'onboarding.billing.biller.country_placeholder',
                                            )}
                                        />
                                    </div>
                                </div>
                            )}

                            <button
                                type="button"
                                onClick={() =>
                                    setSelectedPaymentMethod('ideal_wero')
                                }
                                className={[
                                    'w-full border-b border-input px-4 py-3 text-left text-sm transition-all',
                                    selectedPaymentMethod === 'ideal_wero'
                                        ? 'border-l-2 border-l-primary bg-primary/10 font-medium text-primary'
                                        : 'text-muted-foreground hover:bg-accent/20 hover:text-foreground',
                                ].join(' ')}
                            >
                                {t('onboarding.billing.methods.ideal_wero')}
                            </button>
                            {selectedPaymentMethod === 'ideal_wero' && (
                                <div className="space-y-3 border-b border-input px-4 py-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="billing-name">
                                            {t(
                                                'onboarding.billing.biller.name',
                                            )}
                                        </Label>
                                        <Input
                                            id="billing-name"
                                            value={form.data.billing_name}
                                            className="bg-background"
                                            onChange={(event) =>
                                                form.setData(
                                                    'billing_name',
                                                    event.target.value,
                                                )
                                            }
                                            placeholder={t(
                                                'onboarding.billing.biller.name_placeholder',
                                            )}
                                        />
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        {t(
                                            'onboarding.billing.methods.external_redirect_note',
                                        )}
                                    </p>
                                </div>
                            )}

                            <button
                                type="button"
                                onClick={() => setSelectedPaymentMethod('sepa')}
                                className={[
                                    'w-full border-b border-input px-4 py-3 text-left text-sm transition-all',
                                    selectedPaymentMethod === 'sepa'
                                        ? 'border-l-2 border-l-primary bg-primary/10 font-medium text-primary'
                                        : 'text-muted-foreground hover:bg-accent/20 hover:text-foreground',
                                ].join(' ')}
                            >
                                {t('onboarding.billing.methods.sepa')}
                            </button>
                            {selectedPaymentMethod === 'sepa' && (
                                <div className="space-y-3 border-b border-input px-4 py-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="billing-iban">
                                            {t(
                                                'onboarding.billing.biller.iban',
                                            )}
                                        </Label>
                                        <Input
                                            id="billing-iban"
                                            value={form.data.billing_vat}
                                            className="bg-background"
                                            onChange={(event) =>
                                                form.setData(
                                                    'billing_vat',
                                                    event.target.value,
                                                )
                                            }
                                            placeholder={t(
                                                'onboarding.billing.biller.iban_placeholder',
                                            )}
                                        />
                                    </div>
                                    <div className="grid gap-3 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="billing-email">
                                                {t(
                                                    'onboarding.billing.biller.email',
                                                )}
                                            </Label>
                                            <Input
                                                id="billing-email"
                                                type="email"
                                                value={form.data.billing_email}
                                                className="bg-background"
                                                onChange={(event) =>
                                                    form.setData(
                                                        'billing_email',
                                                        event.target.value,
                                                    )
                                                }
                                                placeholder={t(
                                                    'onboarding.billing.biller.email_placeholder',
                                                )}
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="billing-name-sepa">
                                                {t(
                                                    'onboarding.billing.biller.name',
                                                )}
                                            </Label>
                                            <Input
                                                id="billing-name-sepa"
                                                value={form.data.billing_name}
                                                className="bg-background"
                                                onChange={(event) =>
                                                    form.setData(
                                                        'billing_name',
                                                        event.target.value,
                                                    )
                                                }
                                                placeholder={t(
                                                    'onboarding.billing.biller.name_placeholder',
                                                )}
                                            />
                                        </div>
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        {t(
                                            'onboarding.billing.methods.sepa_mandate_note',
                                        )}
                                    </p>
                                </div>
                            )}

                            <button
                                type="button"
                                onClick={() =>
                                    setSelectedPaymentMethod('paypal')
                                }
                                className={[
                                    'w-full px-4 py-3 text-left text-sm transition-all',
                                    selectedPaymentMethod === 'paypal'
                                        ? 'border-l-2 border-l-primary bg-primary/10 font-medium text-primary'
                                        : 'text-muted-foreground hover:bg-accent/20 hover:text-foreground',
                                ].join(' ')}
                            >
                                {t('onboarding.billing.methods.paypal')}
                            </button>
                        </div>

                        <p className="text-xs text-muted-foreground">
                            {t('onboarding.billing.card.secure_note')}
                        </p>
                        {stripeError !== '' && (
                            <p className="text-sm text-red-600 dark:text-red-400">
                                {stripeError}
                            </p>
                        )}
                        <InputError message={form.errors.payment_method} />
                    </div>

                    <div className="space-y-3">
                        <p className="text-sm font-medium text-foreground">
                            {t('onboarding.billing.seats.label')}
                        </p>
                        <div className="rounded-md border border-input p-3">
                            <div className="mt-2 grid grid-cols-[44px_1fr_44px] gap-2">
                                <button
                                    type="button"
                                    className="h-10 rounded-md border border-input px-3 text-lg font-semibold transition-all hover:bg-accent/20"
                                    onClick={() =>
                                        setSeatQuantity((value) => {
                                            const nextValue = Math.max(1, value - 1);
                                            form.setData('seats', nextValue);

                                            return nextValue;
                                        })
                                    }
                                >
                                    -
                                </button>
                                <Input
                                    id="seat-quantity"
                                    type="number"
                                    min={1}
                                    value={seatQuantity}
                                    className="h-10 text-center"
                                    onChange={(event) => {
                                        const parsed = Number.parseInt(event.target.value, 10);
                                        const nextValue = Number.isNaN(parsed) ? 1 : Math.max(1, parsed);
                                        setSeatQuantity(nextValue);
                                        form.setData('seats', nextValue);
                                    }}
                                />
                                <button
                                    type="button"
                                    className="h-10 rounded-md border border-input px-3 text-lg font-semibold transition-all hover:bg-accent/20"
                                    onClick={() =>
                                        setSeatQuantity((value) => {
                                            const nextValue = value + 1;
                                            form.setData('seats', nextValue);

                                            return nextValue;
                                        })
                                    }
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        <div className="rounded-md border border-input bg-card p-4">
                            <p className="text-sm font-medium text-foreground">
                                {t('onboarding.billing.summary.title')}
                            </p>

                            <div className="space-y-3 border-b border-input pb-4">
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="text-sm font-medium">
                                            {t(
                                                `onboarding.billing.plans.${selectedPlanData?.slug}.name`,
                                            )}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            {t(
                                                'onboarding.billing.summary.recurring',
                                            )}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            {t('onboarding.billing.summary.seats', {
                                                quantity: seatQuantity,
                                            })}
                                        </p>
                                    </div>
                                    <p className="text-sm">
                                        {formattedAmount(
                                            subtotalAmount,
                                        )}
                                    </p>
                                </div>
                            </div>

                            <div className="space-y-2 pt-4 text-sm">
                                <div className="flex items-center justify-between text-muted-foreground">
                                    <span>
                                        {t(
                                            'onboarding.billing.summary.subtotal',
                                        )}
                                    </span>
                                    <span>
                                        {formattedAmount(
                                            subtotalAmount,
                                        )}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between text-muted-foreground">
                                    <span>
                                        {t('onboarding.billing.summary.tax')}
                                    </span>
                                    <span>
                                        {t(
                                            'onboarding.billing.summary.tax_included',
                                        )}
                                    </span>
                                </div>
                            </div>

                            <div className="mt-4 flex items-center justify-between border-t border-input pt-4">
                                <span className="text-sm font-semibold">
                                    {t('onboarding.billing.summary.total')}
                                </span>
                                <span className="text-sm font-semibold">
                                    {formattedAmount(
                                        subtotalAmount,
                                    )}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {currentStep === 'plan' ? (
                <Button
                    type="button"
                    className="w-full"
                    disabled={form.processing}
                    onClick={goToNextStep}
                >
                    {form.processing && <Spinner />}
                    {requiresPayment
                        ? t('onboarding.billing.actions.continue_to_payment')
                        : t('onboarding.billing.submit')}
                </Button>
            ) : (
                <div className="flex flex-col gap-3 sm:flex-row">
                    <Button
                        type="button"
                        variant="outline"
                        className="w-full"
                        onClick={() => setCurrentStep('plan')}
                        disabled={form.processing}
                    >
                        {t('onboarding.billing.actions.back_to_plan')}
                    </Button>
                    <Button
                        type="submit"
                        className="w-full"
                        disabled={form.processing}
                    >
                        {form.processing && <Spinner />}
                        {t('onboarding.billing.actions.complete_purchase')}
                    </Button>
                </div>
            )}
        </form>
    );
}

export default function Billing({ plans, stripeKey, workspace }: Props) {
    const { t } = useTranslation();
    const stripePromise = useMemo(
        () => (stripeKey !== null ? loadStripe(stripeKey) : null),
        [stripeKey],
    );

    return (
        <AuthCardLayout
            width="max-w-5xl"
            title={t('onboarding.billing.meta.title')}
            description={t('onboarding.billing.meta.description')}
        >
            <Head title={t('onboarding.billing.meta.title')} />

            {stripePromise !== null ? (
                <Elements key={stripeKey} stripe={stripePromise}>
                    <BillingForm
                        plans={plans}
                        workspace={workspace}
                        stripeEnabled={stripeKey !== null}
                    />
                </Elements>
            ) : (
                <BillingForm
                    plans={plans}
                    workspace={workspace}
                    stripeEnabled={false}
                />
            )}
        </AuthCardLayout>
    );
}
