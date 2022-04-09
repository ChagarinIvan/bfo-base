require('./bootstrap');

import { createApp } from 'vue'
import { i18nVue } from 'laravel-vue-i18n'
import Persons from "../vue/components/person/Persons.vue";

let app = createApp({});
app.component('persons', Persons)
app.use(i18nVue, {
    resolve: lang => import(`../lang/${lang}.json`),
})
app.mount("#app");

require('./scripts');
