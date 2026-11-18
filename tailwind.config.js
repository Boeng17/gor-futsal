import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                cream: '#E2D6C6',
                paper: '#FBF7F0',
                ink: '#232B20',
                accent: '#6C4830',
                accentDark: '#543724',
                sand: '#D6C8B3',
                maint: '#E8D6AE',
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Lora', 'Georgia', ...defaultTheme.fontFamily.serif],
                mono: ['DM Mono', ...defaultTheme.fontFamily.mono],
            },
        },
    },

    plugins: [forms],
};
