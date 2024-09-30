import './bootstrap.js';
import '../css/app.css';
import { createPinia } from 'pinia';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

//Quasar CSS
import { Quasar, Notify } from 'quasar'

// Import icon libraries
import '@quasar/extras/material-icons/material-icons.css'
import iconSet from 'quasar/icon-set/fontawesome-v6'
import '@quasar/extras/fontawesome-v6/fontawesome-v6.css'

import '@fortawesome/fontawesome-free/css/all.min.css'; // Ensure correct path
import '@fortawesome/fontawesome-free/js/all.js'; // Ensure correct path

// Import Quasar css
import 'quasar/src/css/index.sass'
const appName = import.meta.env.VITE_APP_NAME || 'Artful Mind';
const pinia = createPinia();


createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(Quasar, {
                plugins: {Notify},
                iconSet: iconSet // import Quasar plugins and add here
              })
            .use(pinia)
            .mount(el);

            // Set default theme
            
    },
    progress: {
        color: '#4B5563',
    },
});
