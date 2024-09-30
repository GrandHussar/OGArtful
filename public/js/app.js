import '/bootstrap.js';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// PrimeVue CSS
import PrimeVue from 'primevue/config';
import Carousel from 'primevue/carousel';
import Aura from '@primevue/themes/aura';

// Quasar CSS
import { Quasar } from 'quasar';

// Import icon libraries
import '@quasar/extras/material-icons/material-icons.css';
import iconSet from 'quasar/icon-set/fontawesome-v6';
import '@quasar/extras/fontawesome-v6/fontawesome-v6.css';

// Import Quasar CSS
import 'quasar/src/css/index.sass';

// Import FontAwesome
import '@fortawesome/fontawesome-free/css/all.min.css'; // Ensure correct path
import '@fortawesome/fontawesome-free/js/all.js'; // Ensure correct path

const appName = import.meta.env.VITE_APP_NAME || 'Artful Mind';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(Quasar, {
                plugins: {},
                iconSet: iconSet // Import Quasar plugins and add here
            })
            .use(PrimeVue, {
                ripple: true
            })
            .component('Carousel', Carousel)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
