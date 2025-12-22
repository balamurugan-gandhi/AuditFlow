import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './style.css'
import App from './App.vue'
import router from './router'
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import Tooltip from 'primevue/tooltip';
import 'primeicons/primeicons.css'

const app = createApp(App);
const pinia = createPinia();

import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';

app.use(ToastService);
app.use(ConfirmationService);
app.use(pinia);
app.use(router);
app.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            darkModeSelector: '.dark',
        }
    }
});
app.directive('tooltip', Tooltip);
app.mount('#app');
