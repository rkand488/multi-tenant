/**
 * Design Tokens & Theme Configuration
 * Hexagonal Multi-Tenant Cube Brand Identity
 *
 * @description Centralized theme configuration for the SaaS platform
 * @version 1.0.0
 */

export const brandColors = {
    // Core Brand Colors
    deepBlue: '#1E3A8A',
    royalBlue: '#2563EB',
    teal: '#06B6D4',
    indigo: '#7C3AED',
};

export const colors = {
    // Primary (Royal Blue)
    primary: {
        50: '#eff6ff',
        100: '#dbeafe',
        200: '#bfdbfe',
        300: '#93c5fd',
        400: '#60a5fa',
        500: '#3b82f6',
        600: '#2563eb',  // Main primary
        700: '#1d4ed8',
        800: '#1e3a8a',  // Deep blue
        900: '#1e293b',
        950: '#0f172a',
    },
    
    // Secondary (Teal)
    secondary: {
        50: '#ecfeff',
        100: '#cffafe',
        200: '#a5f3fc',
        300: '#67e8f9',
        400: '#22d3ee',
        500: '#06b6d4',  // Main secondary
        600: '#0891b2',
        700: '#0e7490',
        800: '#155e75',
        900: '#164e63',
        950: '#083344',
    },
    
    // Accent (Indigo)
    accent: {
        50: '#faf5ff',
        100: '#f3e8ff',
        200: '#e9d5ff',
        300: '#d8b4fe',
        400: '#c084fc',
        500: '#a855f7',
        600: '#7c3aed',  // Main accent
        700: '#6d28d9',
        800: '#5b21b6',
        900: '#4c1d95',
        950: '#2e1065',
    },
    
    // Status Colors
    success: {
        50: '#f0fdf4',
        100: '#dcfce7',
        200: '#bbf7d0',
        300: '#86efac',
        400: '#4ade80',
        500: '#22c55e',
        600: '#16a34a',
        700: '#15803d',
        800: '#166534',
        900: '#14532d',
    },
    
    warning: {
        50: '#fffbeb',
        100: '#fef3c7',
        200: '#fde68a',
        300: '#fcd34d',
        400: '#fbbf24',
        500: '#f59e0b',
        600: '#d97706',
        700: '#b45309',
        800: '#92400e',
        900: '#78350f',
    },
    
    error: {
        50: '#fef2f2',
        100: '#fee2e2',
        200: '#fecaca',
        300: '#fca5a5',
        400: '#f87171',
        500: '#ef4444',
        600: '#dc2626',
        700: '#b91c1c',
        800: '#991b1b',
        900: '#7f1d1d',
    },
    
    // Neutral
    gray: {
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
};

export const gradients = {
    primary: 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #06b6d4 100%)',
    hero: 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 35%, #06b6d4 70%, #7c3aed 100%)',
    accent: 'linear-gradient(135deg, #2563eb 0%, #7c3aed 100%)',
    subtle: 'linear-gradient(180deg, rgba(37, 99, 235, 0.05) 0%, rgba(6, 182, 212, 0.05) 100%)',
    overlay: 'linear-gradient(180deg, rgba(15, 23, 42, 0) 0%, rgba(15, 23, 42, 0.8) 100%)',
};

export const shadows = {
    sm: '0 1px 2px 0 rgba(30, 58, 138, 0.05)',
    md: '0 4px 6px -1px rgba(30, 58, 138, 0.1), 0 2px 4px -1px rgba(30, 58, 138, 0.06)',
    lg: '0 10px 15px -3px rgba(30, 58, 138, 0.1), 0 4px 6px -2px rgba(30, 58, 138, 0.05)',
    xl: '0 20px 25px -5px rgba(30, 58, 138, 0.1), 0 10px 10px -5px rgba(30, 58, 138, 0.04)',
    '2xl': '0 25px 50px -12px rgba(30, 58, 138, 0.25)',
    inner: 'inset 0 2px 4px 0 rgba(30, 58, 138, 0.06)',
};

export const spacing = {
    xs: '0.25rem',   // 4px
    sm: '0.5rem',    // 8px
    md: '1rem',      // 16px
    lg: '1.5rem',    // 24px
    xl: '2rem',      // 32px
    '2xl': '3rem',   // 48px
    '3xl': '4rem',   // 64px
    '4xl': '6rem',   // 96px
    '5xl': '8rem',   // 128px
};

export const radius = {
    sm: '0.375rem',  // 6px
    md: '0.5rem',    // 8px
    lg: '0.75rem',   // 12px
    xl: '1rem',      // 16px
    '2xl': '1.5rem', // 24px
    full: '9999px',
};

export const typography = {
    fontFamily: {
        sans: "'Instrument Sans', ui-sans-serif, system-ui, sans-serif",
    },
    fontSize: {
        xs: ['0.75rem', { lineHeight: '1rem' }],
        sm: ['0.875rem', { lineHeight: '1.25rem' }],
        base: ['1rem', { lineHeight: '1.5rem' }],
        lg: ['1.125rem', { lineHeight: '1.75rem' }],
        xl: ['1.25rem', { lineHeight: '1.75rem' }],
        '2xl': ['1.5rem', { lineHeight: '2rem' }],
        '3xl': ['1.875rem', { lineHeight: '2.25rem' }],
        '4xl': ['2.25rem', { lineHeight: '2.5rem' }],
        '5xl': ['3rem', { lineHeight: '1' }],
        '6xl': ['3.75rem', { lineHeight: '1' }],
    },
    fontWeight: {
        normal: '400',
        medium: '500',
        semibold: '600',
        bold: '700',
        extrabold: '800',
    },
};

export const zIndex = {
    dropdown: 1000,
    sticky: 1020,
    fixed: 1030,
    modalBackdrop: 1040,
    modal: 1050,
    popover: 1060,
    tooltip: 1070,
};

export const breakpoints = {
    sm: '640px',
    md: '768px',
    lg: '1024px',
    xl: '1280px',
    '2xl': '1536px',
};

export const animation = {
    duration: {
        fast: '150ms',
        base: '200ms',
        slow: '300ms',
        slower: '500ms',
    },
    easing: {
        default: 'cubic-bezier(0.4, 0, 0.2, 1)',
        in: 'cubic-bezier(0.4, 0, 1, 1)',
        out: 'cubic-bezier(0, 0, 0.2, 1)',
        inOut: 'cubic-bezier(0.4, 0, 0.2, 1)',
    },
};

/**
 * Chart.js Theme Configuration
 * Brand-aware color palette for data visualization
 */
export const chartTheme = {
    colors: [
        '#2563eb', // Royal Blue
        '#06b6d4', // Teal
        '#7c3aed', // Indigo
        '#1e3a8a', // Deep Blue
        '#22c55e', // Success Green
        '#f59e0b', // Warning Orange
        '#ef4444', // Error Red
        '#8b5cf6', // Purple
    ],
    
    backgroundColor: [
        'rgba(37, 99, 235, 0.1)',
        'rgba(6, 182, 212, 0.1)',
        'rgba(124, 58, 237, 0.1)',
        'rgba(30, 58, 138, 0.1)',
        'rgba(34, 197, 94, 0.1)',
        'rgba(245, 158, 11, 0.1)',
        'rgba(239, 68, 68, 0.1)',
        'rgba(139, 92, 246, 0.1)',
    ],
    
    borderColor: [
        '#2563eb',
        '#06b6d4',
        '#7c3aed',
        '#1e3a8a',
        '#22c55e',
        '#f59e0b',
        '#ef4444',
        '#8b5cf6',
    ],
    
    grid: {
        color: 'rgba(229, 231, 235, 0.3)',
        borderColor: 'rgba(229, 231, 235, 0.5)',
    },
    
    tooltip: {
        backgroundColor: 'rgba(15, 23, 42, 0.95)',
        titleColor: '#f1f5f9',
        bodyColor: '#cbd5e1',
        borderColor: '#334155',
    },
};

/**
 * Dark Mode Theme
 */
export const darkMode = {
    background: '#0f172a',
    backgroundSecondary: '#1e293b',
    backgroundTertiary: '#334155',
    surface: '#1e293b',
    border: '#334155',
    borderSecondary: '#475569',
    text: '#f1f5f9',
    textSecondary: '#cbd5e1',
    textTertiary: '#94a3b8',
};

/**
 * Component Variants
 */
export const components = {
    button: {
        primary: {
            bg: colors.primary[600],
            hover: colors.primary[700],
            active: colors.primary[800],
            text: '#ffffff',
        },
        secondary: {
            bg: colors.secondary[500],
            hover: colors.secondary[600],
            active: colors.secondary[700],
            text: '#ffffff',
        },
        accent: {
            bg: colors.accent[600],
            hover: colors.accent[700],
            active: colors.accent[800],
            text: '#ffffff',
        },
        ghost: {
            bg: 'transparent',
            hover: colors.gray[100],
            active: colors.gray[200],
            text: colors.gray[700],
        },
    },
    
    card: {
        bg: '#ffffff',
        border: colors.gray[200],
        shadow: shadows.md,
        hoverShadow: shadows.lg,
    },
    
    input: {
        bg: '#ffffff',
        border: colors.gray[300],
        focusBorder: colors.primary[600],
        focusRing: `0 0 0 3px ${colors.primary[600]}20`,
        text: colors.gray[900],
        placeholder: colors.gray[400],
    },
    
    sidebar: {
        bg: colors.primary[800],
        hoverBg: colors.primary[700],
        activeBg: colors.primary[900],
        text: colors.gray[200],
        activeText: '#ffffff',
        border: colors.primary[700],
    },
};

/**
 * Helper Functions
 */
export const hexToRgba = (hex, alpha = 1) => {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};

export const getGradientStyle = (gradientKey) => {
    return { background: gradients[gradientKey] };
};

export const getTextGradientStyle = (gradientKey) => {
    return {
        background: gradients[gradientKey],
        WebkitBackgroundClip: 'text',
        WebkitTextFillColor: 'transparent',
        backgroundClip: 'text',
    };
};

/**
 * Export all as default
 */
export default {
    brandColors,
    colors,
    gradients,
    shadows,
    spacing,
    radius,
    typography,
    zIndex,
    breakpoints,
    animation,
    chartTheme,
    darkMode,
    components,
    hexToRgba,
    getGradientStyle,
    getTextGradientStyle,
};
