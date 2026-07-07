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
                display: ['Playfair Display', 'Georgia', 'serif'],
            },
            colors: {
                brand: {
                    50:  '#F2F9EE',   // Fond très clair vert
                    100: '#D9EDD0',   // Vert très pâle
                    200: '#B3D9A0',   // Vert pâle
                    300: '#7DBF66',   // Vert moyen
                    400: '#3D9426',   // Vert intermédiaire
                    500: '#1E690F',   // 🟢 Vert exact du logo (dominant)
                    600: '#185709',   // Vert foncé
                    700: '#114006',   // Vert très foncé
                    800: '#0A2903',   // Vert nuit
                    900: '#051601',   // Quasi noir vert
                },
                gold: {
                    50:  '#FFFBEA',
                    100: '#FFF3C0',
                    200: '#FFE880',
                    300: '#FFD940',
                    400: '#F0D21E',   // 🟡 Or clair du logo
                    500: '#F0C31E',   // 🟡 Or exact du logo (dominant)
                    600: '#C9A014',
                    700: '#997A0E',
                    800: '#665208',
                    900: '#332904',
                },
            },
            boxShadow: {
                'brand':    '0 4px 24px 0 rgba(30, 105, 15, 0.20)',
                'brand-lg': '0 8px 40px 0 rgba(30, 105, 15, 0.28)',
                'gold':     '0 4px 24px 0 rgba(240, 195, 30, 0.30)',
            },
        },
    },
    plugins: [
        require('@tailwindcss/typography'),
    ],
};
