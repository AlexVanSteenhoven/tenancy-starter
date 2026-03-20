import { Head, useForm } from '@inertiajs/react';
import { type SubmitEventHandler } from 'react';
import { useTranslation } from 'react-i18next';
import LoginController from '@/actions/App/Http/Controllers/Admin/Auth/LoginController';
import AuthCardLayout from '@/layouts/auth/auth-card-layout';
import InputError from '@components/input-error';
import { Button } from '@components/ui/button';
import { Checkbox } from '@components/ui/checkbox';
import { Input } from '@components/ui/input';
import { Label } from '@components/ui/label';
import '@lib/i18n';

type Props = {
    status?: string | null;
};

export default function AdminLogin({ status = null }: Props) {
    const { t } = useTranslation();
    const form = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit: SubmitEventHandler<HTMLFormElement> = (event) => {
        event.preventDefault();

        form.post(LoginController.url(), {
            onFinish: () => {
                form.reset('password');
            },
        });
    };

    return (
        <AuthCardLayout
            title={t('admin.auth.meta.title')}
            description={t('admin.auth.meta.description')}
        >
            <Head title={t('admin.auth.meta.title')} />

            <form onSubmit={submit} className="space-y-4">
                <div className="space-y-2">
                    <Label htmlFor="email">
                        {t('admin.auth.form.email.label')}
                    </Label>
                    <Input
                        id="email"
                        type="email"
                        value={form.data.email}
                        onChange={(event) =>
                            form.setData('email', event.target.value)
                        }
                        placeholder={t('admin.auth.form.email.placeholder')}
                    />
                    <InputError message={form.errors.email} />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="password">
                        {t('admin.auth.form.password.label')}
                    </Label>
                    <Input
                        id="password"
                        type="password"
                        value={form.data.password}
                        onChange={(event) =>
                            form.setData('password', event.target.value)
                        }
                        placeholder={t('admin.auth.form.password.placeholder')}
                    />
                    <InputError message={form.errors.password} />
                </div>

                <div className="flex items-center gap-2">
                    <Checkbox
                        id="remember"
                        checked={form.data.remember}
                        onCheckedChange={(checked) =>
                            form.setData('remember', Boolean(checked))
                        }
                    />
                    <Label htmlFor="remember">
                        {t('admin.auth.form.remember')}
                    </Label>
                </div>

                {status !== null && (
                    <p className="text-sm text-muted-foreground">{status}</p>
                )}

                <Button
                    type="submit"
                    className="w-full"
                    disabled={form.processing}
                >
                    {t('admin.auth.form.submit')}
                </Button>
            </form>
        </AuthCardLayout>
    );
}
