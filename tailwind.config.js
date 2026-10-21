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
            fontFamily: {
                sans: ['Cairo', 'Tajawal', 'Segoe UI', 'Tahoma', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                edu: {
                    50: '#F4FBF6',
                    100: '#E8F7EE',
                    200: '#CDEBD9',
                    300: '#A3D9B8',
                    400: '#62B987',
                    500: '#3E9F65',
                    600: '#2F8A4F',
                    700: '#277043', // hover
                    800: '#1F5A36',
                    900: '#17432A',
                },
                ink: {
                    DEFAULT: '#1B2A3A',
                    soft: '#3C4B5C',
                    muted: '#5E6A78',
                    line: '#E3EAE6',
                },
                topbar: '#1C2530',
                sun: {
                    DEFAULT: '#F5B301',
                    soft: '#FFF6D6',
                },
            },
            borderRadius: {
                card: '14px',
            },
            boxShadow: {
                card: '0 1px 2px rgba(27, 42, 58, 0.04), 0 4px 14px rgba(27, 42, 58, 0.05)',
                pop: '0 12px 40px rgba(27, 42, 58, 0.18)',
            },
        },
    },

    plugins: [forms],
};
