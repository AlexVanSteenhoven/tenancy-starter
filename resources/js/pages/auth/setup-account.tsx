import { Form, Head } from '@inertiajs/react';
import InputError from '@components/input-error';
import { Button } from '@components/ui/button';
import { Input } from '@components/ui/input';
import { Label } from '@components/ui/label';
import { Spinner } from '@components/ui/spinner';
import AuthCardLayout from '@/layouts/auth/auth-card-layout';
import StoreSetupAccountController from '@/actions/App/Http/Controllers/Onboarding/StoreSetupAccountController';
import '@/lib/i18n';
import { useTranslation } from 'react-i18next';

type Props = {
    email: string;
};

export default function SetupAccount({ email }: Props) {
    const { t } = useTranslation();

    return (
        <AuthCardLayout
            title={t('setup.account.meta.title')}
            description={t('setup.account.meta.description')}
        >
            <Head title={t('setup.account.meta.title')} />

            <Form
                {...StoreSetupAccountController.form()}
                resetOnSuccess={['password', 'password_confirmation']}
                disableWhileProcessing
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    {t('setup.account.form.email.label')}
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    defaultValue={email}
                                    readOnly
                                    tabIndex={1}
                                    autoComplete="email"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="name">
                                    {t('setup.account.form.name.label')}
                                </Label>
                                <Input
                                    id="name"
                                    type="text"
                                    name="name"
                                    required
                                    autoFocus
                                    tabIndex={2}
                                    autoComplete="name"
                                    placeholder={t('setup.account.form.name.placeholder')}
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">
                                    {t('setup.account.form.password.label')}
                                </Label>
                                <Input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    tabIndex={3}
                                    autoComplete="new-password"
                                    placeholder={t('setup.account.form.password.placeholder')}
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    {t('setup.account.form.password_confirmation.label')}
                                </Label>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    tabIndex={4}
                                    autoComplete="new-password"
                                    placeholder={t('setup.account.form.password_confirmation.placeholder')}
                                />
                                <InputError message={errors.password_confirmation} />
                            </div>

                            <Button
                                type="submit"
                                className="mt-2 w-full"
                                tabIndex={5}
                                disabled={processing}
                            >
                                {processing && <Spinner />}
                                {t('setup.account.form.submit')}
                            </Button>
                        </div>
                    </>
                )}
            </Form>
        </AuthCardLayout>
    );
}
