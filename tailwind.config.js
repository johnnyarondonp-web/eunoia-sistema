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
                //  paleta de colores
                'eunoia-coral': '#F27B73',      // (botones/links)
                'eunoia-crema': '#FFF8F0',     // Fondo de las tarjetas de productos
                'eunoia-bg': '#F5F5F7',        // Fondo general 
                'eunoia-text': '#4F4A4A',     // Gris/marrón oscuro 
            },
        },
    },

    plugins: [forms],
};
