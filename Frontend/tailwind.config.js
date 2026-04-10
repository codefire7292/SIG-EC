/** @type {import('tailwindcss').Config} */
export default {
    content: [
        '../Backend/resources/views/**/*.blade.php',
        './src/**/*.vue',
        './src/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            },
        },
    },
    plugins: [
        require('@tailwindcss/typography'),
    ],
};
