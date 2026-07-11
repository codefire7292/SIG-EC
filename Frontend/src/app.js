import './bootstrap';
import './app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { Ziggy } from './ziggy';
import { ensureCsrf } from '@/services/api';

const appName = import.meta.env.VITE_APP_NAME || 'SIG-EC';

// Ajuster dynamiquement l'URL de base de Ziggy selon l'hôte actuel (localhost en dev, sig-ec.creinit.com en prod)
if (typeof window !== 'undefined') {
    Ziggy.url = window.location.origin;
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    async setup({ el, App, props, plugin }) {
        // Initialize CSRF for Axios stateful requests
        try {
            await ensureCsrf();
        } catch (e) {
            console.error('CSRF initialization failed:', e);
        }
        
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .mount(el);
    },
    progress: {
        color: '#2563eb', // Blue-600
    },
});
