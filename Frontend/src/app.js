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

    // Traduction globale des messages d'erreur natifs du navigateur (HTML5 validation) en français
    window.addEventListener('invalid', function (e) {
        const target = e.target;
        if (!target) return;

        // Réinitialiser d'abord la validité personnalisée pour lire l'état de validité réel de l'élément
        target.setCustomValidity('');

        if (!target.validity || target.validity.valid) return;

        let message = 'Veuillez saisir une valeur valide.';

        if (target.validity.valueMissing) {
            message = 'Veuillez renseigner ce champ.';
        } else if (target.validity.typeMismatch) {
            if (target.type === 'email') {
                message = 'Veuillez saisir une adresse e-mail valide.';
            } else if (target.type === 'url') {
                message = 'Veuillez saisir une URL valide.';
            }
        } else if (target.validity.patternMismatch) {
            message = 'Veuillez respecter le format requis.';
        } else if (target.validity.tooShort) {
            message = `Veuillez augmenter la longueur de ce texte à au moins ${target.minLength} caractères.`;
        } else if (target.validity.tooLong) {
            message = `Veuillez réduire la longueur de ce texte à au plus ${target.maxLength} caractères.`;
        } else if (target.validity.rangeUnderflow) {
            message = `La valeur doit être supérieure ou égale à ${target.min}.`;
        } else if (target.validity.rangeOverflow) {
            message = `La valeur doit être inférieure ou égale à ${target.max}.`;
        } else if (target.validity.stepMismatch) {
            message = 'Veuillez choisir une valeur valide.';
        } else if (target.validity.badInput) {
            message = 'Veuillez saisir une valeur valide.';
        }

        target.setCustomValidity(message);
    }, true);

    // Effacer le message d'erreur dès que l'utilisateur commence à corriger la saisie
    window.addEventListener('input', function (e) {
        e.target.setCustomValidity('');
    }, true);

    window.addEventListener('change', function (e) {
        e.target.setCustomValidity('');
    }, true);
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
