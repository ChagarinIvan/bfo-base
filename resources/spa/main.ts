import { createApp } from 'vue'
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import Tooltip from 'primevue/tooltip'
import Aura from '@primevue/themes/aura'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import 'primeicons/primeicons.css'
import '@fortawesome/fontawesome-free/css/all.min.css'
import './styles.css'

document.title = 'OrientBase'

createApp(App)
    .use(createPinia())
    .use(router)
    .use(PrimeVue, {
        locale: { firstDayOfWeek: 1 },
        theme: { preset: Aura },
    })
    .use(ToastService)
    .directive('tooltip', Tooltip)
    .mount('#app')
