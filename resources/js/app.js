import './bootstrap.js';
import '../css/app.css';
import { createPinia } from 'pinia';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Quasar CSS
import { Quasar, Notify } from 'quasar'

// Import icon libraries
import '@quasar/extras/material-icons/material-icons.css'
import iconSet from 'quasar/icon-set/fontawesome-v6'
import '@quasar/extras/fontawesome-v6/fontawesome-v6.css'

import '@fortawesome/fontawesome-free/css/all.min.css'; // Ensure correct path
import '@fortawesome/fontawesome-free/js/all.js'; // Ensure correct path

import VCalendar from 'v-calendar';
import 'v-calendar/style.css';

// Import Quasar css
import 'quasar/src/css/index.sass'

// Import Axios
import axios from 'axios';

// Import Vue Toastification
import Toast, { POSITION } from 'vue-toastification'
import 'vue-toastification/dist/index.css'

// Initialize Pinia
const pinia = createPinia();

// Set Axios base URL to root
axios.defaults.baseURL = '/';

// Include credentials for session-based authentication
axios.defaults.withCredentials = true;


// Requested-With Header
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const appName = import.meta.env.VITE_APP_NAME || 'Artful Mind';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(Quasar, {
                plugins: { Notify },
                iconSet: iconSet // import Quasar plugins and add here
            })
            .use(pinia)
            .use(VCalendar, {})
            .use(Toast, {
                position: POSITION.TOP_RIGHT,
                timeout: 5000,
            })
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
