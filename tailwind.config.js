import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            colors: {
                // Semantic surface & ink
                surface: {
                    DEFAULT: 'var(--surface)',
                    raised: 'var(--surface-raised)',
                    sunken: 'var(--surface-sunken)',
                },
                ink: {
                    DEFAULT: 'var(--ink)',
                    muted: 'var(--ink-muted)',
                },
                line: 'var(--line)',
                focus: 'var(--focus)',

                // Semantic brand & accent
                brand: {
                    50: 'var(--brand-50)',
                    100: 'var(--brand-100)',
                    200: 'var(--brand-200)',
                    300: 'var(--brand-300)',
                    400: 'var(--brand-400)',
                    500: 'var(--brand-500)',
                    600: 'var(--brand-600)',
                    700: 'var(--brand-700)',
                    800: 'var(--brand-800)',
                    900: 'var(--brand-900)',
                    fg: 'var(--brand-fg)',
                    DEFAULT: 'var(--brand-600)',
                },
                accent: {
                    50: 'var(--accent-50)',
                    100: 'var(--accent-100)',
                    200: 'var(--accent-200)',
                    300: 'var(--accent-300)',
                    400: 'var(--accent-400)',
                    500: 'var(--accent-500)',
                    600: 'var(--accent-600)',
                    700: 'var(--accent-700)',
                    800: 'var(--accent-800)',
                    900: 'var(--accent-900)',
                    fg: 'var(--accent-fg)',
                    DEFAULT: 'var(--accent-600)',
                },

                // Semantic status tokens
                status: {
                    'success-bg': 'var(--success-bg)',
                    'success-fg': 'var(--success-fg)',
                    'success-line': 'var(--success-line)',
                    'warning-bg': 'var(--warning-bg)',
                    'warning-fg': 'var(--warning-fg)',
                    'warning-line': 'var(--warning-line)',
                    'danger-bg': 'var(--danger-bg)',
                    'danger-fg': 'var(--danger-fg)',
                    'danger-line': 'var(--danger-line)',
                    'info-bg': 'var(--info-bg)',
                    'info-fg': 'var(--info-fg)',
                    'info-line': 'var(--info-line)',
                },
            },
            borderRadius: {
                card: 'var(--radius-card)',
                control: 'var(--radius-control)',
            },
            boxShadow: {
                card: 'var(--shadow-card)',
                pop: 'var(--shadow-pop)',
            },
            fontFamily: {
                sans: ['var(--font-body)', ...defaultTheme.fontFamily.sans],
                display: ['var(--font-display)', ...defaultTheme.fontFamily.sans],
                body: ['var(--font-body)', ...defaultTheme.fontFamily.sans],
                numeric: ['var(--font-numeric)', ...defaultTheme.fontFamily.mono],
            },
        },
    },

    plugins: [forms],
};
