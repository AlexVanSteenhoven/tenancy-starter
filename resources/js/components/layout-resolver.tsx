import { usePage } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin-layout';
import AppLayout from '@/layouts/app-layout';
import type { AppLayoutProps } from '@types';

export function LayoutResolver({ children, ...props }: AppLayoutProps) {
    const { isAdmin } = usePage().props;

    const Layout = isAdmin ? AdminLayout : AppLayout;

    return <Layout {...props}>{children}</Layout>;
}
