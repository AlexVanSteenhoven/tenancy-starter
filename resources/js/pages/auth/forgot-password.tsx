// Components
import { Form, Head } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import InputError from '@components/input-error';
import TextLink from '@components/text-link';
import { Button } from '@components/ui/button';
import { Input } from '@components/ui/input';
import { Label } from '@components/ui/label';
import AuthCardLayout from '@/layouts/auth/auth-card-layout';
import { login } from '@/routes';
import { email } from '@/routes/password';
import '@lib/i18n';

export default function ForgotPassword({ status }: { status?: string }) {
    const { t } = useTranslation();

    return (
        <AuthCardLayout
            title={t('auth.forgot-password.title')}
            description={t('auth.forgot-password.description')}
        >
            <Head title={t('auth.forgot-password.meta.title')} />

            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <div className="space-y-6">
                <Form {...email.form()}>
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    {t('auth.forgot-password.form.email.label')}
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    autoComplete="off"
                                    autoFocus
                                    placeholder={t(
                                        'auth.forgot-password.form.email.placeholder',
                                    )}
                                />

                                <InputError message={errors.email} />
                            </div>

                            <div className="my-6 flex items-center justify-start">
                                <Button
                                    className="w-full"
                                    disabled={processing}
                                    data-test="email-password-reset-link-button"
                                >
                                    {processing && (
                                        <LoaderCircle className="h-4 w-4 animate-spin" />
                                    )}
                                    {t('auth.forgot-password.form.submit')}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>

                <div className="space-x-1 text-center text-sm text-muted-foreground">
                    <span>{t('auth.forgot-password.messages.return-to')}</span>
                    <TextLink href={login()}>
                        {t('auth.forgot-password.actions.log-in')}
                    </TextLink>
                </div>
            </div>
        </AuthCardLayout>
    );
}
