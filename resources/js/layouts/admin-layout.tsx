import { Link, usePage } from '@inertiajs/react';
import { Building2, CreditCard, LayoutGrid, ReceiptText, Tags } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import ShowInvoicesController from '@/actions/App/Http/Controllers/Admin/Invoices/ShowInvoicesController';
import ShowPlansController from '@/actions/App/Http/Controllers/Admin/Plans/ShowPlansController';
import ShowDashboardController from '@/actions/App/Http/Controllers/Admin/ShowDashboardController';
import ShowSubscriptionsController from '@/actions/App/Http/Controllers/Admin/Subscriptions/ShowSubscriptionsController';
import ShowWorkspacesController from '@/actions/App/Http/Controllers/Admin/Workspaces/ShowWorkspacesController';
import { AppContent } from '@components/app-content';
import AppLogo from '@components/app-logo';
import { AppShell } from '@components/app-shell';
import { AppSidebarHeader } from '@components/app-sidebar-header';
import { NavUser } from '@components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@components/ui/sidebar';
import { useCurrentUrl } from '@hooks/use-current-url';
import type { AppLayoutProps, Auth, NavItem } from '@types';
import '@lib/i18n';

const adminNavItems: NavItem[] = [
    { title: 'admin.navigation.dashboard', href: ShowDashboardController.url(), icon: LayoutGrid },
    { title: 'admin.navigation.workspaces', href: ShowWorkspacesController.url(), icon: Building2 },
    { title: 'admin.navigation.plans', href: ShowPlansController.url(), icon: Tags },
    { title: 'admin.navigation.subscriptions', href: ShowSubscriptionsController.url(), icon: CreditCard },
    { title: 'admin.navigation.invoices', href: ShowInvoicesController.url(), icon: ReceiptText },
];

export default function AdminLayout({ children, breadcrumbs = [] }: AppLayoutProps) {
    const { auth } = usePage<{ auth: Auth }>().props;
    const { t } = useTranslation();
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <AppShell variant="sidebar">
            <Sidebar collapsible="icon" variant="inset">
                <SidebarHeader>
                    <SidebarMenu>
                        <SidebarMenuItem>
                            <SidebarMenuButton size="lg" asChild>
                                <Link href={ShowDashboardController.url()}>
                                    <AppLogo />
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarHeader>
                <SidebarContent>
                    <SidebarGroup className="px-2 py-0">
                        <SidebarGroupLabel>{t('admin.navigation.group')}</SidebarGroupLabel>
                        <SidebarMenu>
                            {adminNavItems.map((item) => (
                                <SidebarMenuItem key={item.href.toString()}>
                                    <SidebarMenuButton
                                        asChild
                                        isActive={isCurrentUrl(item.href)}
                                        tooltip={{ children: t(item.title) }}
                                    >
                                        <Link href={item.href}>
                                            {item.icon && <item.icon />}
                                            <span>{t(item.title)}</span>
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            ))}
                        </SidebarMenu>
                    </SidebarGroup>
                </SidebarContent>
                <SidebarFooter>
                    {auth.user !== null && <NavUser />}
                </SidebarFooter>
            </Sidebar>
            <AppContent variant="sidebar" className="overflow-x-hidden">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                {children}
            </AppContent>
        </AppShell>
    );
}
