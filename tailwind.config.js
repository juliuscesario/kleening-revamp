import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    DEFAULT: '#E05A26',
                    dark: '#C84A1C',
                    light: '#FFF5F0',
                },
            },
            fontFamily: {
                sans: ['"Inter"', ...defaultTheme.fontFamily.sans],
                display: ['"Plus Jakarta Sans"', 'sans-serif'],
            },
            borderRadius: {
                'pb': '12px',
            },
            boxShadow: {
                'apple': '0 8px 32px 0 rgba(31, 38, 135, 0.07)',
                'apple-hover': '0 8px 32px 0 rgba(31, 38, 135, 0.15)',
            },
            backdropBlur: {
                'xs': '2px',
            }
        },
    },

    plugins: [forms],
};
