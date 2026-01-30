import { useEffect } from 'react';
import { BRAND_COLORS, TAILWIND_COLORS, DARK_MODE_COLORS, SIDEBAR_COLORS, getChartColors } from '@/config/brand-colors';

/**
 * Custom hook to apply SARH brand colors to the application
 * This ensures consistent brand identity throughout the app
 */
export function useBrandColors() {
    useEffect(() => {
        const root = document.documentElement;
        const isDark = root.classList.contains('dark');

        // Apply CSS variables for brand colors
        const colors = isDark ? DARK_MODE_COLORS : TAILWIND_COLORS;

        // Set primary brand colors
        root.style.setProperty('--brand-primary', BRAND_COLORS.orange[600]);
        root.style.setProperty('--brand-primary-light', BRAND_COLORS.orange[100]);
        root.style.setProperty('--brand-primary-dark', BRAND_COLORS.orange[700]);

        // Set secondary brand colors
        root.style.setProperty('--brand-secondary', BRAND_COLORS.black[700]);
        root.style.setProperty('--brand-secondary-light', BRAND_COLORS.black[100]);

        // Set accent colors
        root.style.setProperty('--brand-accent', BRAND_COLORS.orange[400]);

        // Listen for theme changes
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const newIsDark = root.classList.contains('dark');
                    if (newIsDark !== isDark) {
                        // Theme changed, colors will be updated by CSS
                    }
                }
            });
        });

        observer.observe(root, {
            attributes: true,
            attributeFilter: ['class'],
        });

        return () => observer.disconnect();
    }, []);

    return {
        colors: BRAND_COLORS,
        tailwindColors: TAILWIND_COLORS,
        darkModeColors: DARK_MODE_COLORS,
        sidebarColors: SIDEBAR_COLORS,
    };
}

/**
 * Get appropriate color based on theme
 */
export function useThemedColor(lightColor: string, darkColor: string): string {
    const isDark = typeof window !== 'undefined' ? document.documentElement.classList.contains('dark') : false;
    return isDark ? darkColor : lightColor;
}

/**
 * Get chart colors based on theme
 */
export function useChartColors() {
    const isDark = typeof window !== 'undefined' ? document.documentElement.classList.contains('dark') : false;
    return getChartColors(isDark);
}

/**
 * Apply a specific color to an element
 */
export function applyBrandColor(element: HTMLElement | null, colorType: 'primary' | 'secondary' | 'accent') {
    if (!element) return;

    const isDark = document.documentElement.classList.contains('dark');

    switch (colorType) {
        case 'primary':
            element.style.color = isDark ? BRAND_COLORS.orange[500] : BRAND_COLORS.orange[600];
            break;
        case 'secondary':
            element.style.color = isDark ? BRAND_COLORS.black[300] : BRAND_COLORS.black[700];
            break;
        case 'accent':
            element.style.color = BRAND_COLORS.orange[400];
            break;
    }
}

/**
 * Get brand color class names
 */
export const brandColorClasses = {
    primary: 'text-orange-600 dark:text-orange-500',
    primaryLight: 'text-orange-400 dark:text-orange-300',
    primaryDark: 'text-orange-700 dark:text-orange-600',
    secondary: 'text-black-700 dark:text-black-300',
    secondaryLight: 'text-black-500 dark:text-black-400',
    accent: 'text-orange-400 dark:text-orange-300',
    background: 'bg-orange-50 dark:bg-black-900',
    backgroundLight: 'bg-orange-100 dark:bg-black-800',
    border: 'border-orange-200 dark:border-orange-700',
    borderLight: 'border-orange-100 dark:border-orange-800',
};

export default useBrandColors;
