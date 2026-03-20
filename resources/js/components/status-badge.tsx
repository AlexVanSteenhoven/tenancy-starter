import { useTranslation } from 'react-i18next';
import { statusTranslationMap, Status } from '@/types/enums';
import { Badge } from '@components/ui/badge';
import { useEnumTranslation } from '@hooks/use-enum-translation';
import { useLabel } from '@hooks/use-label';
import { cn } from '@lib/utils';

type StatusBadgeProps = {
    status: Status | string | null | undefined;
    fallbackText?: string;
};

const isStatus = (value: string): value is Status =>
    value === Status.ACTIVE ||
    value === Status.DELETED ||
    value === Status.INACTIVE ||
    value === Status.PENDING ||
    value === Status.BLOCKED ||
    value === Status.SUSPENDED;

const statusBadgeStyles: Record<Status, { badge: string; dot: string }> = {
    [Status.ACTIVE]: {
        badge: 'border-emerald-500/40 bg-emerald-500/10 text-emerald-700 dark:border-emerald-400/40 dark:bg-emerald-400/10 dark:text-emerald-300',
        dot: 'bg-emerald-600 dark:bg-emerald-300',
    },
    [Status.DELETED]: {
        badge: 'border-rose-500/40 bg-rose-500/10 text-rose-700 dark:border-rose-400/40 dark:bg-rose-400/10 dark:text-rose-300',
        dot: 'bg-rose-600 dark:bg-rose-300',
    },
    [Status.INACTIVE]: {
        badge: 'border-zinc-500/40 bg-zinc-500/10 text-zinc-700 dark:border-zinc-400/40 dark:bg-zinc-400/10 dark:text-zinc-300',
        dot: 'bg-zinc-600 dark:bg-zinc-300',
    },
    [Status.PENDING]: {
        badge: 'border-amber-500/40 bg-amber-500/10 text-amber-700 dark:border-amber-400/40 dark:bg-amber-400/10 dark:text-amber-300',
        dot: 'bg-amber-600 dark:bg-amber-300',
    },
    [Status.BLOCKED]: {
        badge: 'border-red-500/40 bg-red-500/10 text-red-700 dark:border-red-400/40 dark:bg-red-400/10 dark:text-red-300',
        dot: 'bg-red-600 dark:bg-red-300',
    },
    [Status.SUSPENDED]: {
        badge: 'border-orange-500/40 bg-orange-500/10 text-orange-700 dark:border-orange-400/40 dark:bg-orange-400/10 dark:text-orange-300',
        dot: 'bg-orange-600 dark:bg-orange-300',
    },
};

export default function StatusBadge({
    status,
    fallbackText,
}: StatusBadgeProps) {
    const { t } = useTranslation();
    const translateStatus = useEnumTranslation(statusTranslationMap);
    const { getLabel } = useLabel();

    if (!status) {
        return <span>{fallbackText ?? t('users.columns.no-status')}</span>;
    }

    if (!isStatus(status)) {
        return <span>{getLabel(status, translateStatus)}</span>;
    }

    const styles = statusBadgeStyles[status];

    return (
        <Badge
            variant="outline"
            className={cn(
                'rounded-full px-2.5 py-0.5 font-medium',
                styles.badge,
            )}
        >
            <span
                className={cn(
                    'mr-1.5 inline-block size-1.5 rounded-full',
                    styles.dot,
                )}
            />
            {getLabel(status, translateStatus)}
        </Badge>
    );
}
