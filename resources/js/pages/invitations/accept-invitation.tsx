import { Head, useForm } from '@inertiajs/react';
import { type SubmitEventHandler } from 'react';
import { useTranslation } from 'react-i18next';
import StoreAcceptInvitationController from '@/actions/App/Http/Controllers/Invitations/StoreAcceptInvitationController';
import AuthCardLayout from '@/layouts/auth/auth-card-layout';
import InputError from '@components/input-error';
import { Button } from '@components/ui/button';
import { Input } from '@components/ui/input';
import { Label } from '@components/ui/label';
import '@/lib/i18n';
import { useLabel } from '@hooks/use-label';

type InvitationData = {
    email: string;
    role: string;
    invitedBy: string | null;
};

type AcceptInvitationProps = {
    token: string;
    invitation: InvitationData | null;
};

export default function AcceptInvitation({
    token,
    invitation,
}: AcceptInvitationProps) {
    const { t } = useTranslation();
    const { getLabel, translateRole } = useLabel();
    const form = useForm({
        email: invitation?.email ?? '',
        name: '',
        password: '',
        password_confirmation: '',
    });

    const submit: SubmitEventHandler<HTMLFormElement> = (event) => {
        event.preventDefault();

        form.post(StoreAcceptInvitationController.url(token), {
            onSuccess: () => form.reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthCardLayout
            title={t('auth.invitations.meta.title')}
            description={
                invitation
                    ? t('auth.invitations.states.summary', {
                          inviter:
                              invitation.invitedBy ??
                              t('auth.invitations.states.inviter_unknown'),
                          role: getLabel(invitation.role, translateRole),
                      })
                    : t('auth.invitations.states.invalid')
            }
        >
            <Head title={t('auth.invitations.meta.title')} />

            {!invitation ? (
                <p className="text-muted-foreground text-sm">
                    {t('auth.invitations.states.invalid')}
                </p>
            ) : (
                <form onSubmit={submit} className="flex flex-col gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="email">{t('auth.invitations.form.email.label')}</Label>
                        <Input
                            id="email"
                            type="email"
                            value={form.data.email}
                            readOnly
                            autoComplete="email"
                        />
                        <InputError message={form.errors.email} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="name">{t('auth.invitations.form.name.label')}</Label>
                        <Input
                            id="name"
                            type="text"
                            value={form.data.name}
                            onChange={(event) =>
                                form.setData('name', event.target.value)
                            }
                            placeholder={t('auth.invitations.form.name.placeholder')}
                            autoComplete="name"
                            autoFocus
                        />
                        <InputError message={form.errors.name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">
                            {t('auth.invitations.form.password.label')}
                        </Label>
                        <Input
                            id="password"
                            type="password"
                            value={form.data.password}
                            onChange={(event) =>
                                form.setData('password', event.target.value)
                            }
                            placeholder={t('auth.invitations.form.password.placeholder')}
                            autoComplete="new-password"
                        />
                        <InputError message={form.errors.password} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation">
                            {t('auth.invitations.form.password_confirmation.label')}
                        </Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            value={form.data.password_confirmation}
                            onChange={(event) =>
                                form.setData(
                                    'password_confirmation',
                                    event.target.value,
                                )
                            }
                            placeholder={t(
                                'auth.invitations.form.password_confirmation.placeholder',
                            )}
                            autoComplete="new-password"
                        />
                        <InputError message={form.errors.password_confirmation} />
                    </div>

                    <Button type="submit" disabled={form.processing}>
                        {t('auth.invitations.form.submit')}
                    </Button>
                </form>
            )}
        </AuthCardLayout>
    );
}
