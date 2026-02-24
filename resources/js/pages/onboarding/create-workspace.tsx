import { Form, Head } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import AuthCardLayout from '@/layouts/auth/auth-card-layout';
import { store } from '@/routes/onboarding/create-workspace';
import InputError from '@components/input-error';
import { Button } from '@components/ui/button';
import { Input } from '@components/ui/input';
import { Label } from '@components/ui/label';
import { Spinner } from '@components/ui/spinner';

type CreateWorkspacePageProps = {
    status?: string;
};

const formatToSubdomain = (value: string): string => {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]/g, '-')
        .replace(/^-+|-+$/g, '');
};

export default function CreateWorkspace({ status }: CreateWorkspacePageProps) {
    const { t } = useTranslation();
    const [workspace, setWorkspace] = useState<string>('');

    const subdomain = useMemo(() => formatToSubdomain(workspace), [workspace]);

    return (
        <AuthCardLayout
            title={t('onboarding.meta.title')}
            description={t('onboarding.meta.description')}
        >
            <Head title={t('onboarding.meta.title')} />

            <div className="space-y-4">
                {status && (
                    <div className="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300">
                        {status}
                    </div>
                )}

                <Form
                    {...store.form()}
                    disableWhileProcessing
                    className="flex flex-col gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="workspace">
                                    {t('onboarding.form.workspace_name.label')}
                                </Label>
                                <Input
                                    id="workspace"
                                    type="text"
                                    name="workspace"
                                    required
                                    autoFocus
                                    autoComplete="organization"
                                    placeholder={t(
                                        'onboarding.form.workspace_name.placeholder',
                                    )}
                                    onChange={(event) =>
                                        setWorkspace(event.target.value)
                                    }
                                />
                                <InputError message={errors.workspace} />

                                <p className="text-sm text-slate-500 dark:text-slate-400">
                                    {subdomain
                                        ? t('onboarding.preview.value', {
                                              domain: `${subdomain}.tenancy.test`,
                                          })
                                        : t('onboarding.preview.empty')}
                                </p>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    {t('onboarding.form.email.label')}
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autoComplete="email"
                                    placeholder={t(
                                        'onboarding.form.email.placeholder',
                                    )}
                                />
                                <InputError message={errors.email} />
                            </div>

                            <Button
                                type="submit"
                                className="w-full"
                                disabled={processing}
                            >
                                {processing && <Spinner />}
                                {t('onboarding.form.submit')}
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </AuthCardLayout>
    );
}
