import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                kwsp: {
                    deep: 'var(--kwsp-blue-deep)',
                    action: 'var(--kwsp-blue-action)',
                    light: 'var(--kwsp-blue-light)',
                    pale: 'var(--kwsp-blue-pale)',
                    red: 'var(--kwsp-red)',
                    yellow: 'var(--kwsp-yellow)',
                    'yellow-text': 'var(--kwsp-yellow-text)',
                    green: 'var(--kwsp-green)',
                    'green-text': 'var(--kwsp-green-text)',
                    'green-pale': 'var(--kwsp-green-pale)',
                },
                text: {
                    main: 'var(--text-main)',
                    secondary: 'var(--text-secondary)',
                    muted: 'var(--text-muted)',
                    faint: 'var(--text-faint)',
                },
            },
            lineHeight: {
                'body': 'var(--leading-body)',
                'heading': 'var(--leading-heading)',
            },
        },
    },

    plugins: [forms],
};
