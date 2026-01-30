import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { useLayout } from '@/contexts/LayoutContext';
import { useSidebarSettings } from '@/contexts/SidebarContext';
import { useBrand } from '@/contexts/BrandContext';
import { type NavItem } from '@/types';
import { Link, usePage, router } from '@inertiajs/react';
import { BookOpen, Folder, LayoutGrid, ShoppingBag, Users, Tag, FileIcon, Settings, BarChart, Barcode, FileText, Briefcase, CheckSquare, Calendar, CreditCard, Ticket, Gift, DollarSign, MessageSquare, CalendarDays, Palette, Image, Mail, Mail as VCard, ChevronDown, Building2, Globe, Clock, Timer, Coins, MapPin, DoorOpen, Lock, Shield, Brain, Trophy, Wifi, Smartphone, TrendingUp } from 'lucide-react';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import AppLogo from './app-logo';
import { useEffect, useState, useRef } from 'react';
import { useTranslation } from 'react-i18next';
import { hasPermission } from '@/utils/authorization';
import { toast } from '@/components/custom-toast';
import { getImagePath } from '@/utils/helpers';


export function AppSidebar() {
    const { t, i18n } = useTranslation();
    const { auth, globalSettings } = usePage().props as any;
    const userRole = auth.user?.type || auth.user?.role;
    const permissions = auth?.permissions || [];
    const isSaas = globalSettings?.is_saas;

    // Get current direction
    const isRtl = document.documentElement.dir === 'rtl';

    // Business switch handler removed

    const getSuperAdminNavItems = (): NavItem[] => [
        {
            title: t('Dashboard'),
            href: route('dashboard'),
            icon: LayoutGrid,
        },

        {
            title: t('Currencies'),
            href: route('currencies.index'),
            icon: DollarSign,
        },
        {
            title: t('Settings'),
            href: route('settings'),
            icon: Settings,
        }
    ];

    const getCompanyNavItems = (): NavItem[] => {
        const items: NavItem[] = [];
        // Dashboard - only show if user has dashboard permission
        if (hasPermission(permissions, 'manage-dashboard')) {
            items.push({
                title: t('Dashboard'),
                href: route('dashboard'),
                icon: LayoutGrid,
            });
        }



        // Staff section - only show if user has any staff-related permissions
        const staffChildren = [];
        if (hasPermission(permissions, 'manage-users')) {
            staffChildren.push({
                title: t('Users'),
                href: route('users.index')
            });
        }
        if (hasPermission(permissions, 'manage-roles')) {
            staffChildren.push({
                title: t('Roles'),
                href: route('roles.index')
            });
        }
        if (staffChildren.length > 0) {
            items.push({
                title: t('Staff'),
                icon: Users,
                children: staffChildren
            });
        }

        // Other menu items with permission checks

        // HR Module
        const hrChildren = [];
        if (hasPermission(permissions, 'manage-branches')) {
            hrChildren.push({
                title: t('Branches'),
                href: route('hr.branches.index')
            });
        }

        if (hasPermission(permissions, 'manage-departments')) {
            hrChildren.push({
                title: t('Departments'),
                href: route('hr.departments.index')
            });
        }



        if (hasPermission(permissions, 'manage-designations')) {
            hrChildren.push({
                title: t('Designations'),
                href: route('hr.designations.index')
            });
        }

        if (hasPermission(permissions, 'manage-document-types')) {
            hrChildren.push({
                title: t('Document Types'),
                href: route('hr.document-types.index')
            });
        }

        if (hasPermission(permissions, 'manage-employees')) {
            hrChildren.push({
                title: t('Employees'),
                href: route('hr.employees.index')
            });
        }

        if (hasPermission(permissions, 'manage-award-types')) {
            hrChildren.push({
                title: t('Award Types'),
                href: route('hr.award-types.index')
            });
        }

        if (hasPermission(permissions, 'manage-awards')) {
            hrChildren.push({
                title: t('Awards'),
                href: route('hr.awards.index')
            });
        }

        if (hasPermission(permissions, 'manage-promotions')) {
            hrChildren.push({
                title: t('Promotions'),
                href: route('hr.promotions.index')
            });
        }


        // Performance Module
        const performanceChildren = [];

        if (hasPermission(permissions, 'manage-performance-indicator-categories')) {
            performanceChildren.push({
                title: t('Indicator Categories'),
                href: route('hr.performance.indicator-categories.index')
            });
        }

        if (hasPermission(permissions, 'manage-performance-indicators')) {
            performanceChildren.push({
                title: t('Indicators'),
                href: route('hr.performance.indicators.index')
            });
        }

        if (hasPermission(permissions, 'manage-goal-types')) {
            performanceChildren.push({
                title: t('Goal Types'),
                href: route('hr.performance.goal-types.index')
            });
        }

        if (hasPermission(permissions, 'manage-employee-goals')) {
            performanceChildren.push({
                title: t('Employee Goals'),
                href: route('hr.performance.employee-goals.index')
            });
        }

        if (hasPermission(permissions, 'manage-review-cycles')) {
            performanceChildren.push({
                title: t('Review Cycles'),
                href: route('hr.performance.review-cycles.index')
            });
        }



        if (hasPermission(permissions, 'manage-employee-reviews')) {
            performanceChildren.push({
                title: t('Employee Reviews'),
                href: route('hr.performance.employee-reviews.index')
            });
        }

        if (performanceChildren.length > 0) {
            hrChildren.push({
                title: t('Performance'),
                children: performanceChildren
            });
        }

        if (hasPermission(permissions, 'manage-resignations')) {
            hrChildren.push({
                title: t('Resignations'),
                href: route('hr.resignations.index')
            });
        }

        if (hasPermission(permissions, 'manage-terminations')) {
            hrChildren.push({
                title: t('Terminations'),
                href: route('hr.terminations.index')
            });
        }

        if (hasPermission(permissions, 'manage-warnings')) {
            hrChildren.push({
                title: t('Warnings'),
                href: route('hr.warnings.index')
            });
        }

        if (hasPermission(permissions, 'manage-complaints')) {
            hrChildren.push({
                title: t('Complaints'),
                href: route('hr.complaints.index')
            });
        }

        if (hasPermission(permissions, 'manage-holidays')) {
            hrChildren.push({
                title: t('Holidays'),
                href: route('hr.holidays.index')
            });
        }

        if (hasPermission(permissions, 'manage-announcements')) {
            hrChildren.push({
                title: t('Announcements'),
                href: route('hr.announcements.index')
            });
        }

        // Asset Management submenu
        const assetChildren = [];

        if (hasPermission(permissions, 'manage-asset-types')) {
            assetChildren.push({
                title: t('Asset Types'),
                href: route('hr.asset-types.index')
            });
        }

        if (hasPermission(permissions, 'manage-assets')) {
            assetChildren.push({
                title: t('Assets'),
                href: route('hr.assets.index')
            });
        }

        if (hasPermission(permissions, 'manage-assets')) {
            assetChildren.push({
                title: t('Dashboard'),
                href: route('hr.assets.dashboard')
            });
        }

        if (hasPermission(permissions, 'manage-assets')) {
            assetChildren.push({
                title: t('Depreciation'),
                href: route('hr.assets.depreciation-report')
            });
        }

        if (assetChildren.length > 0) {
            hrChildren.push({
                title: t('Asset Management'),
                children: assetChildren
            });
        }

        // Add HR Management to items if it has children
        if (hrChildren.length > 0) {
            items.push({
                title: t('HR Management'),
                icon: Briefcase,
                children: hrChildren
            });
        }

        // Training Management submenu - REMOVED
        // Recruitment Management - REMOVED
        // Meeting Management - REMOVED
        // Payroll Management - REMOVED
        // Plans section - REMOVED
        // Referral Program - REMOVED
        // Landing Page - REMOVED
        // Media Library - REMOVED

        // Contract Management as separate menu
        const contractChildren = [];

        if (hasPermission(permissions, 'manage-contract-types')) {
            contractChildren.push({
                title: t('Contract Types'),
                href: route('hr.contracts.contract-types.index')
            });
        }

        if (hasPermission(permissions, 'manage-employee-contracts')) {
            contractChildren.push({
                title: t('Employee Contracts'),
                href: route('hr.contracts.employee-contracts.index')
            });
        }



        if (hasPermission(permissions, 'manage-contract-renewals')) {
            contractChildren.push({
                title: t('Contract Renewals'),
                href: route('hr.contracts.contract-renewals.index')
            });
        }

        if (hasPermission(permissions, 'manage-contract-templates')) {
            contractChildren.push({
                title: t('Contract Templates'),
                href: route('hr.contracts.contract-templates.index')
            });
        }

        if (contractChildren.length > 0) {
            items.push({
                title: t('Contract Management'),
                icon: FileText,
                children: contractChildren
            });
        }

        // Document Management as separate menu
        const documentChildren = [];

        if (hasPermission(permissions, 'manage-document-categories')) {
            documentChildren.push({
                title: t('Document Categories'),
                href: route('hr.documents.document-categories.index')
            });
        }

        if (hasPermission(permissions, 'manage-hr-documents')) {
            documentChildren.push({
                title: t('HR Documents'),
                href: route('hr.documents.hr-documents.index')
            });
        }



        if (hasPermission(permissions, 'manage-document-acknowledgments')) {
            documentChildren.push({
                title: t('Acknowledgments'),
                href: route('hr.documents.document-acknowledgments.index')
            });
        }

        if (hasPermission(permissions, 'manage-document-templates')) {
            documentChildren.push({
                title: t('Document Templates'),
                href: route('hr.documents.document-templates.index')
            });
        }

        if (documentChildren.length > 0) {
            items.push({
                title: t('Document Management'),
                icon: Folder,
                children: documentChildren
            });
        }



        // Meeting Management submenu - REMOVED



        if (hasPermission(permissions, 'view-calendar') || hasPermission(permissions, 'manage-calendar')) {
            items.push({
                title: t('Calendar'),
                href: route('calendar.index'),
                icon: Calendar,
            });
        }

        // Leave Management as separate menu
        const leaveChildren = [];

        if (hasPermission(permissions, 'manage-leave-types')) {
            leaveChildren.push({
                title: t('Leave Types'),
                href: route('hr.leave-types.index')
            });
        }

        if (hasPermission(permissions, 'manage-leave-policies')) {
            leaveChildren.push({
                title: t('Leave Policies'),
                href: route('hr.leave-policies.index')
            });
        }

        if (hasPermission(permissions, 'manage-leave-applications')) {
            leaveChildren.push({
                title: t('Leave Applications'),
                href: route('hr.leave-applications.index')
            });
        }

        if (hasPermission(permissions, 'manage-leave-balances')) {
            leaveChildren.push({
                title: t('Leave Balances'),
                href: route('hr.leave-balances.index')
            });
        }

        if (leaveChildren.length > 0) {
            items.push({
                title: t('Leave Management'),
                icon: CalendarDays,
                children: leaveChildren
            });
        }

        // Attendance Management as separate menu
        const attendanceChildren = [];

        if (hasPermission(permissions, 'manage-shifts')) {
            attendanceChildren.push({
                title: t('Shifts'),
                href: route('hr.shifts.index')
            });
        }

        if (hasPermission(permissions, 'manage-attendance-policies')) {
            attendanceChildren.push({
                title: t('Attendance Policies'),
                href: route('hr.attendance-policies.index')
            });
        }

        if (hasPermission(permissions, 'manage-attendance-records')) {
            attendanceChildren.push({
                title: t('Attendance Records'),
                href: route('hr.attendance-records.index')
            });
        }

        if (hasPermission(permissions, 'manage-attendance-regularizations')) {
            attendanceChildren.push({
                title: t('Attendance Regularizations'),
                href: route('hr.attendance-regularizations.index')
            });
        }

        if (attendanceChildren.length > 0) {
            items.push({
                title: t('Attendance Management'),
                icon: Clock,
                children: attendanceChildren
            });
        }

        // =====================================================
        // Phase 1: Quick Attendance Features
        // =====================================================
        const quickAttendanceChildren = [];
        
        if (hasPermission(permissions, 'manage-attendance-records')) {
            quickAttendanceChildren.push({
                title: t('Quick Check-in'),
                href: '/attendance/quick-checkin'
            });
            quickAttendanceChildren.push({
                title: t('Bulk Check-in'),
                href: '/attendance/bulk-checkin'
            });
            quickAttendanceChildren.push({
                title: t('Live Status'),
                href: '/attendance/live-status'
            });
        }
        
        if (hasPermission(permissions, 'manage-branches')) {
            quickAttendanceChildren.push({
                title: t('WiFi Networks'),
                href: '/hr/wifi-networks'
            });
            quickAttendanceChildren.push({
                title: t('Time Windows'),
                href: '/hr/time-windows'
            });
            quickAttendanceChildren.push({
                title: t('Deduction Tiers'),
                href: '/hr/deduction-tiers'
            });
        }
        
        if (quickAttendanceChildren.length > 0) {
            items.push({
                title: t('Quick Attendance'),
                icon: Wifi,
                children: quickAttendanceChildren
            });
        }

        // =====================================================
        // Phase 2: Competition & Gamification
        // =====================================================
        const gamificationChildren = [];
        
        if (hasPermission(permissions, 'view-hr-reports')) {
            gamificationChildren.push({
                title: t('Branch Ranking'),
                href: '/reports/branch-ranking'
            });
            gamificationChildren.push({
                title: t('MVP Leaderboard'),
                href: '/hr/mvp-leaderboard'
            });
        }
        
        if (hasPermission(permissions, 'manage-employees')) {
            gamificationChildren.push({
                title: t('Badges'),
                href: '/hr/badges'
            });
        }
        
        if (hasPermission(permissions, 'manage-settings')) {
            gamificationChildren.push({
                title: t('News Ticker'),
                href: '/settings/news-ticker'
            });
        }
        
        if (gamificationChildren.length > 0) {
            items.push({
                title: t('Gamification'),
                icon: Trophy,
                children: gamificationChildren
            });
        }

        // =====================================================
        // Phase 3: AI Features
        // =====================================================
        const aiChildren = [];
        
        if (hasPermission(permissions, 'view-hr-reports')) {
            aiChildren.push({
                title: t('Risk Predictions'),
                href: '/ai/risk-predictions'
            });
            aiChildren.push({
                title: t('Security Dashboard'),
                href: '/ai/security'
            });
            aiChildren.push({
                title: t('Liveness Logs'),
                href: '/ai/security/liveness-logs'
            });
            aiChildren.push({
                title: t('Tamper Logs'),
                href: '/ai/security/tamper-logs'
            });
            aiChildren.push({
                title: t('Sentiment Analysis'),
                href: '/ai/sentiment'
            });
        }
        
        if (aiChildren.length > 0) {
            items.push({
                title: t('AI Features'),
                icon: Brain,
                children: aiChildren
            });
        }

        // =====================================================
        // Phase 4: Advanced Security
        // =====================================================
        const securityChildren = [];
        
        if (hasPermission(permissions, 'manage-settings')) {
            securityChildren.push({
                title: t('Lockdown Mode'),
                href: '/security/lockdown'
            });
        }
        
        if (hasPermission(permissions, 'view-hr-reports')) {
            securityChildren.push({
                title: t('Audit Logs'),
                href: '/security/audit-logs'
            });
        }
        
        if (hasPermission(permissions, 'manage-branches')) {
            securityChildren.push({
                title: t('Work Zones'),
                href: '/settings/work-zones'
            });
            securityChildren.push({
                title: t('Zone Access Logs'),
                href: '/reports/zone-access-logs'
            });
        }
        
        if (hasPermission(permissions, 'manage-leaves')) {
            securityChildren.push({
                title: t('Exit Permits'),
                href: '/hr/exit-permits'
            });
        }
        
        if (hasPermission(permissions, 'manage-settings')) {
            securityChildren.push({
                title: t('Exit Permit Settings'),
                href: '/settings/exit-permits'
            });
            securityChildren.push({
                title: t('PWA Settings'),
                href: '/settings/pwa'
            });
        }
        
        if (securityChildren.length > 0) {
            items.push({
                title: t('Security & Advanced'),
                icon: Shield,
                children: securityChildren
            });
        }

        // Time Tracking as separate menu
        const timeTrackingChildren = [];

        if (hasPermission(permissions, 'manage-time-entries')) {
            timeTrackingChildren.push({
                title: t('Time Entries'),
                href: route('hr.time-entries.index')
            });
        }

        if (timeTrackingChildren.length > 0) {
            items.push({
                title: t('Time Tracking'),
                icon: Timer,
                children: timeTrackingChildren
            });
        }

        // Payroll Management - REMOVED

        if (hasPermission(permissions, 'manage-settings')) {
            items.push({
                title: t('Settings'),
                href: route('settings'),
                icon: Settings,
            });
        }

        return items;
    };

    const mainNavItems = userRole === 'superadmin' ? getSuperAdminNavItems() : getCompanyNavItems();

    const { position, effectivePosition } = useLayout();
    const { variant, collapsible, style } = useSidebarSettings();
    const { logoLight, logoDark, favicon, updateBrandSettings } = useBrand();
    const [sidebarStyle, setSidebarStyle] = useState({});

    useEffect(() => {

        // Apply styles based on sidebar style
        if (style === 'colored') {
            setSidebarStyle({ backgroundColor: 'var(--primary)', color: 'white' });
        } else if (style === 'gradient') {
            setSidebarStyle({
                background: 'linear-gradient(to bottom, var(--primary), color-mix(in srgb, var(--primary), transparent 20%))',
                color: 'white'
            });
        } else {
            setSidebarStyle({});
        }
    }, [style]);

    const filteredNavItems = mainNavItems;

    // Get the first available menu item's href for logo link
    const getFirstAvailableHref = () => {
        if (filteredNavItems.length === 0) return route('dashboard');

        const firstItem = filteredNavItems[0];
        if (firstItem.href) {
            return firstItem.href;
        } else if (firstItem.children && firstItem.children.length > 0) {
            return firstItem.children[0].href || route('dashboard');
        }
        return route('dashboard');
    };

    return (
        <Sidebar
            side={effectivePosition}
            collapsible={collapsible}
            variant={variant}
            className={style !== 'plain' ? 'sidebar-custom-style' : ''}
        >
            <SidebarHeader className={style !== 'plain' ? 'sidebar-styled' : ''} style={sidebarStyle}>
                <div className="flex justify-center items-center p-2">
                    <Link href={getFirstAvailableHref()} prefetch className="flex items-center justify-center">
                        {/* Logo for expanded sidebar */}
                        <div className="h-12 group-data-[collapsible=icon]:hidden flex items-center">
                            {(() => {
                                const isDark = document.documentElement.classList.contains('dark');
                                const currentLogo = isDark ? logoLight : logoDark;
                                const displayUrl = getImagePath(currentLogo) ?? currentLogo;

                                return displayUrl ? (
                                    <img
                                        key={`${currentLogo}-${Date.now()}`}
                                        src={displayUrl}
                                        alt="Logo"
                                        className="h-9 w-auto max-w-[180px] transition-all duration-200"
                                        onError={() => updateBrandSettings({ [isDark ? 'logoLight' : 'logoDark']: '' })}
                                    />
                                ) : (
                                    <div className="h-12 text-inherit font-semibold flex items-center text-lg tracking-tight">
                                        WorkDo
                                    </div>
                                );
                            })()}
                        </div>

                        {/* Icon for collapsed sidebar */}
                        <div className="h-8 w-8 hidden group-data-[collapsible=icon]:block">
                            {(() => {
                                const displayFavicon = favicon ? getImagePath(favicon) : '';

                                return displayFavicon ? (
                                    <img
                                        key={`${favicon}-${Date.now()}`}
                                        src={displayFavicon}
                                        alt="Icon"
                                        className="h-8 w-8 transition-all duration-200"
                                        onError={() => updateBrandSettings({ favicon: '' })}
                                    />
                                ) : (
                                    <div className="h-8 w-8 bg-primary text-white rounded flex items-center justify-center font-bold shadow-sm">
                                        W
                                    </div>
                                );
                            })()}
                        </div>
                    </Link>
                </div>

                {/* Business Switcher removed */}
            </SidebarHeader>

            <SidebarContent>
                <div style={sidebarStyle} className={`h-full ${style !== 'plain' ? 'sidebar-styled' : ''}`}>
                    <NavMain items={filteredNavItems} position={effectivePosition} />
                </div>
            </SidebarContent>

            <SidebarFooter>
                {/* <NavFooter items={footerNavItems} className="mt-auto" position={position} /> */}
                {/* Profile menu moved to header */}
            </SidebarFooter>
        </Sidebar>
    );
}