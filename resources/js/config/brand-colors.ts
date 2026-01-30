/**
 * SARH Brand Color Configuration
 * Central color system for the application
 * Theme: Orange, White, Black
 */

export const BRAND_COLORS = {
  // Orange Palette (Primary Brand Color)
  orange: {
    50: '#fff8f0',
    100: '#ffe8d6',
    200: '#ffd4ad',
    300: '#ffb884',
    400: '#ff9c5a',
    500: '#ff8531',
    600: '#e67228',
    700: '#cc5f20',
    800: '#b34c17',
    900: '#99390e',
    950: '#7f2605',
  },

  // Black Palette (Secondary & Neutral)
  black: {
    50: '#f9fafb',
    100: '#f3f4f6',
    200: '#e5e7eb',
    300: '#d1d5db',
    400: '#9ca3af',
    500: '#6b7280',
    600: '#4b5563',
    700: '#374151',
    800: '#1f2937',
    900: '#111827',
    950: '#030712',
  },

  // White (Base)
  white: '#ffffff',

  // Status Colors
  success: '#10b981',
  warning: '#f59e0b',
  error: '#ef4444',
  info: '#3b82f6',
};

export const TAILWIND_COLORS = {
  // Light Mode Colors
  primary: BRAND_COLORS.orange[600],
  'primary-dark': BRAND_COLORS.orange[700],
  'primary-light': BRAND_COLORS.orange[100],
  secondary: BRAND_COLORS.black[700],
  'secondary-light': BRAND_COLORS.black[100],
  accent: BRAND_COLORS.orange[400],
  muted: BRAND_COLORS.black[100],
  'muted-foreground': BRAND_COLORS.black[600],
  border: BRAND_COLORS.black[200],
  background: BRAND_COLORS.white,
  foreground: BRAND_COLORS.black[900],
};

export const DARK_MODE_COLORS = {
  // Dark Mode Colors
  primary: BRAND_COLORS.orange[500],
  'primary-dark': BRAND_COLORS.orange[400],
  'primary-light': BRAND_COLORS.orange[200],
  secondary: BRAND_COLORS.black[300],
  'secondary-light': BRAND_COLORS.black[400],
  accent: BRAND_COLORS.orange[400],
  muted: BRAND_COLORS.black[800],
  'muted-foreground': BRAND_COLORS.black[400],
  border: BRAND_COLORS.black[800],
  background: BRAND_COLORS.black[950],
  foreground: BRAND_COLORS.white,
};

export const SIDEBAR_COLORS = {
  light: {
    background: BRAND_COLORS.white,
    foreground: BRAND_COLORS.black[900],
    primary: BRAND_COLORS.orange[600],
    'primary-foreground': BRAND_COLORS.white,
    accent: BRAND_COLORS.orange[100],
    'accent-foreground': BRAND_COLORS.orange[700],
    border: BRAND_COLORS.black[200],
  },
  dark: {
    background: BRAND_COLORS.black[900],
    foreground: BRAND_COLORS.white,
    primary: BRAND_COLORS.orange[500],
    'primary-foreground': BRAND_COLORS.black[950],
    accent: BRAND_COLORS.orange[900],
    'accent-foreground': BRAND_COLORS.orange[300],
    border: BRAND_COLORS.black[800],
  },
};

export const CHART_COLORS = {
  light: [
    BRAND_COLORS.orange[600],
    BRAND_COLORS.orange[400],
    BRAND_COLORS.black[700],
    BRAND_COLORS.black[500],
    BRAND_COLORS.orange[300],
  ],
  dark: [
    BRAND_COLORS.orange[500],
    BRAND_COLORS.orange[300],
    BRAND_COLORS.black[400],
    BRAND_COLORS.black[600],
    BRAND_COLORS.orange[200],
  ],
};

/**
 * Get color by theme mode
 */
export function getColors(isDark: boolean) {
  return isDark ? DARK_MODE_COLORS : TAILWIND_COLORS;
}

/**
 * Get sidebar colors by theme mode
 */
export function getSidebarColors(isDark: boolean) {
  return isDark ? SIDEBAR_COLORS.dark : SIDEBAR_COLORS.light;
}

/**
 * Get chart colors by theme mode
 */
export function getChartColors(isDark: boolean) {
  return isDark ? CHART_COLORS.dark : CHART_COLORS.light;
}
