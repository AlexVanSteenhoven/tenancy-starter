import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import InputError from '@components/input-error';
import { Button } from '@components/ui/button';
import { Input } from '@components/ui/input';
import { Label } from '@components/ui/label';
import AuthCardLayout from '@/layouts/auth/auth-card-layout';
import '@/lib/i18n';
import { useLabel } from '@hooks/use-label';
import { useTranslation } from 'react-i18next';

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

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.post(`/invitations/${token}`, {
            onSuccess: () => form.reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthCardLayout
            title={t('invitations.meta.title')}
            description={
                invitation
                    ? t('invitations.states.summary', {
                          inviter:
                              invitation.invitedBy ??
                              t('invitations.states.inviter_unknown'),
                          role: getLabel(invitation.role, translateRole),
                      })
                    : t('invitations.states.invalid')
            }
        >
            <Head title={t('invitations.meta.title')} />

            {!invitation ? (
                <p className="text-muted-foreground text-sm">
                    {t('invitations.states.invalid')}
                </p>
            ) : (
                <form onSubmit={submit} className="flex flex-col gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="email">{t('invitations.form.email.label')}</Label>
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
                        <Label htmlFor="name">{t('invitations.form.name.label')}</Label>
                        <Input
                            id="name"
                            type="text"
                            value={form.data.name}
                            onChange={(event) =>
                                form.setData('name', event.target.value)
                            }
                            placeholder={t('invitations.form.name.placeholder')}
                            autoComplete="name"
                            autoFocus
                        />
                        <InputError message={form.errors.name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">
                            {t('invitations.form.password.label')}
                        </Label>
                        <Input
                            id="password"
                            type="password"
                            value={form.data.password}
                            onChange={(event) =>
                                form.setData('password', event.target.value)
                            }
                            placeholder={t('invitations.form.password.placeholder')}
                            autoComplete="new-password"
                        />
                        <InputError message={form.errors.password} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation">
                            {t('invitations.form.password_confirmation.label')}
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
                                'invitations.form.password_confirmation.placeholder',
                            )}
                            autoComplete="new-password"
                        />
                        <InputError message={form.errors.password_confirmation} />
                    </div>

                    <Button type="submit" disabled={form.processing}>
                        {t('invitations.form.submit')}
                    </Button>
                </form>
            )}
        </AuthCardLayout>
    );
}
