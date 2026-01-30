import { SidebarProvider } from '@/components/ui/sidebar';
import { useLayout } from '@/contexts/LayoutContext';
import { cn } from '@/lib/utils';
import { useState, useEffect } from 'react';
import CookieConsentBanner from '@/components/CookieConsentBanner';

interface AppShellProps {
    children: React.ReactNode;
    variant?: 'header' | 'sidebar';
}

export function AppShell({ children, variant = 'header' }: AppShellProps) {
    const [isOpen, setIsOpen] = useState(() => (typeof window !== 'undefined' ? localStorage.getItem('sidebar') !== 'false' : true));
    const [isMobile, setIsMobile] = useState(() => (typeof window !== 'undefined' ? window.innerWidth < 1024 : false));

    // Check if device is mobile and close sidebar
    useEffect(() => {
        const checkMobile = () => {
            const mobile = window.innerWidth < 1024;
            setIsMobile(mobile);
            // Close sidebar on mobile by default
            if (mobile && isOpen) {
                setIsOpen(false);
            }
        };

        checkMobile();
        window.addEventListener('resize', checkMobile);
        return () => window.removeEventListener('resize', checkMobile);
    }, []);

    // Auto-close sidebar on navigation (for mobile)
    useEffect(() => {
        const handleNavigation = () => {
            if (isMobile && isOpen) {
                setIsOpen(false);
                localStorage.setItem('sidebar', 'false');
            }
        };

        // Listen for link clicks
        const handleClick = (e: MouseEvent) => {
            const target = e.target as HTMLElement;
            if (target.closest('a') && !target.closest('[role="button"]')) {
                handleNavigation();
            }
        };

        document.addEventListener('click', handleClick);
        return () => document.removeEventListener('click', handleClick);
    }, [isMobile, isOpen]);

    const handleSidebarChange = (open: boolean) => {
        setIsOpen(open);

        if (typeof window !== 'undefined') {
            localStorage.setItem('sidebar', String(open));
        }
    };

    if (variant === 'header') {
        return (
            <div className="flex min-h-screen w-full flex-col">
                {children}
                <CookieConsentBanner />
            </div>
        );
    }

    const { position } = useLayout();

    return (
        <SidebarProvider defaultOpen={isMobile ? false : isOpen} open={isOpen} onOpenChange={handleSidebarChange}>
            <div className={cn('flex w-full', position === 'right' ? 'flex-row-reverse' : 'flex-row')}>
                {children}
                <CookieConsentBanner />
            </div>
        </SidebarProvider>
    );
}
