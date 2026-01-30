import React from 'react';
import { LucideIcon, TrendingUp, TrendingDown } from 'lucide-react';
import { cn } from '@/lib/utils';

interface StatCardProps {
  title: string;
  value: string | number;
  subtitle?: string;
  icon: LucideIcon;
  trend?: {
    value: number;
    isPositive: boolean;
  };
  variant?: 'orange' | 'black' | 'white' | 'gradient';
  className?: string;
}

export function StatCard({
  title,
  value,
  subtitle,
  icon: Icon,
  trend,
  variant = 'white',
  className,
}: StatCardProps) {
  const variants = {
    orange: {
      card: 'bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25',
      icon: 'bg-white/20 text-white',
      title: 'text-orange-100',
      value: 'text-white',
      subtitle: 'text-orange-100',
    },
    black: {
      card: 'bg-gradient-to-br from-gray-800 to-gray-900 text-white shadow-lg shadow-black/25',
      icon: 'bg-white/10 text-white',
      title: 'text-gray-300',
      value: 'text-white',
      subtitle: 'text-gray-400',
    },
    white: {
      card: 'bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-lg shadow-black/5',
      icon: 'bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25',
      title: 'text-gray-500 dark:text-gray-400',
      value: 'text-gray-900 dark:text-white',
      subtitle: 'text-gray-500 dark:text-gray-400',
    },
    gradient: {
      card: 'bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-950/50 dark:to-gray-900 border border-orange-200 dark:border-orange-800/30',
      icon: 'bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25',
      title: 'text-orange-600 dark:text-orange-400',
      value: 'text-gray-900 dark:text-white',
      subtitle: 'text-orange-500 dark:text-orange-400',
    },
  };

  const styles = variants[variant];

  return (
    <div
      className={cn(
        'rounded-2xl p-5 sm:p-6 transition-all duration-300 hover:-translate-y-1 group relative overflow-hidden',
        styles.card,
        className
      )}
    >
      {/* Background Pattern */}
      <div className="absolute top-0 right-0 w-32 h-32 opacity-10 pointer-events-none">
        <svg viewBox="0 0 100 100" className="w-full h-full">
          <circle cx="80" cy="20" r="40" fill="currentColor" />
        </svg>
      </div>

      <div className="relative z-10 flex items-start justify-between">
        <div className="flex-1 min-w-0">
          <p className={cn('text-xs sm:text-sm font-medium', styles.title)}>
            {title}
          </p>
          <p
            className={cn(
              'mt-2 text-2xl sm:text-3xl font-bold tracking-tight',
              styles.value
            )}
          >
            {value}
          </p>
          {(subtitle || trend) && (
            <div className="mt-2 flex items-center gap-2 flex-wrap">
              {trend && (
                <span
                  className={cn(
                    'inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full',
                    trend.isPositive
                      ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                      : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                  )}
                >
                  {trend.isPositive ? (
                    <TrendingUp className="h-3 w-3" />
                  ) : (
                    <TrendingDown className="h-3 w-3" />
                  )}
                  {trend.value}%
                </span>
              )}
              {subtitle && (
                <span className={cn('text-xs', styles.subtitle)}>{subtitle}</span>
              )}
            </div>
          )}
        </div>
        <div
          className={cn(
            'rounded-xl p-2.5 sm:p-3 transition-transform duration-300 group-hover:scale-110 flex-shrink-0',
            styles.icon
          )}
        >
          <Icon className="h-5 w-5 sm:h-6 sm:w-6" />
        </div>
      </div>
    </div>
  );
}

export default StatCard;
