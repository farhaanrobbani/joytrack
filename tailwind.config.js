import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
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
                brand: {
                    50: '#ecfdf5',
                    100: '#d1fae5',
                    500: '#10b981',
                    600: '#059669',
                    700: '#047857',
                    900: '#064e3b',
                },
            },
            borderRadius: {
                '2xl': '1rem',
            },
            boxShadow: {
                soft: '0 2px 10px rgba(0,0,0,0.06)',
                'soft-lg': '0 8px 24px rgba(0,0,0,0.08)',
            },
        },
    },

    plugins: [forms],
};
