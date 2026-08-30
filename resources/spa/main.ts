import { createApp } from 'vue'
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import Aura from '@primevue/themes/aura'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import 'primeicons/primeicons.css'
import { t } from './i18n'
import './styles.css'

document.title = t('spa.competitions.title')

createApp(App)
    .use(createPinia())
    .use(router)
    .use(PrimeVue, { theme: { preset: Aura } })
    .use(ToastService)
    .mount('#app')
