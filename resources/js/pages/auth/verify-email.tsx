// Components
import { Form, Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthCardLayout from '@/layouts/auth/auth-card-layout';
import { logout } from '@/routes';
import { send } from '@/routes/verification';
import TextLink from '@components/text-link';
import { Button } from '@components/ui/button';
import { Spinner } from '@components/ui/spinner';
import '@lib/i18n';

export default function VerifyEmail({ status }: { status?: string }) {
    const { t } = useTranslation();

    return (
        <AuthCardLayout
            title={t('auth.verify-email.title')}
            description={t('auth.verify-email.description')}
        >
            <Head title={t('auth.verify-email.meta.title')} />

            {status === 'verification-link-sent' && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {t('auth.verify-email.messages.verification-link-sent')}
                </div>
            )}

            <Form {...send.form()} className="space-y-6 text-center">
                {({ processing }) => (
                    <>
                        <Button disabled={processing} variant="secondary">
                            {processing && <Spinner />}
                            {t('auth.verify-email.actions.resend')}
                        </Button>

                        <TextLink
                            href={logout()}
                            className="mx-auto block text-sm"
                        >
                            {t('auth.verify-email.actions.logout')}
                        </TextLink>
                    </>
                )}
            </Form>
        </AuthCardLayout>
    );
}
