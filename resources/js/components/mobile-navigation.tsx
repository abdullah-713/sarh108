import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Home, Users, Calendar, Settings, Menu } from 'lucide-react';
import { cn } from '@/lib/utils';
import { useTranslation } from 'react-i18next';

interface MobileNavItem {
  icon: React.ElementType;
  label: string;
  href: string;
  active?: boolean;
}

export function MobileNavigation() {
  const { t } = useTranslation();
  const { url } = usePage();

  const navItems: MobileNavItem[] = [
    {
      icon: Home,
      label: t('Home'),
      href: '/dashboard',
      active: url === '/dashboard' || url === '/',
    },
    {
      icon: Users,
      label: t('Employees'),
      href: '/hr/employees',
      active: url.includes('/hr/employees'),
    },
    {
      icon: Calendar,
      label: t('Attendance'),
      href: '/hr/attendance-records',
      active: url.includes('/attendance'),
    },
    {
      icon: Settings,
      label: t('Settings'),
      href: '/settings',
      active: url.includes('/settings'),
    },
  ];

  return (
    <nav className="fixed bottom-0 left-0 right-0 z-50 md:hidden bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 shadow-[0_-4px_20px_rgba(0,0,0,0.1)] safe-area-pb">
      <div className="flex justify-around items-center h-16 px-2">
        {navItems.map((item) => {
          const Icon = item.icon;
          return (
            <Link
              key={item.href}
              href={item.href}
              className={cn(
                'flex flex-col items-center justify-center flex-1 py-2 px-1 rounded-xl transition-all duration-200',
                item.active
                  ? 'text-orange-500 bg-orange-50 dark:bg-orange-950/50'
                  : 'text-gray-500 dark:text-gray-400 hover:text-orange-500 hover:bg-orange-50/50 dark:hover:bg-orange-950/30'
              )}
            >
              <Icon
                className={cn(
                  'h-5 w-5 mb-1 transition-transform',
                  item.active && 'scale-110'
                )}
              />
              <span className="text-[10px] font-medium truncate max-w-full">
                {item.label}
              </span>
              {item.active && (
                <div className="absolute -top-0.5 w-8 h-1 bg-gradient-to-r from-orange-500 to-orange-400 rounded-full" />
              )}
            </Link>
          );
        })}
      </div>
    </nav>
  );
}

export default MobileNavigation;
