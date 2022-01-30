require('./bootstrap');

import {createApp} from 'vue'
import {i18nVue} from 'laravel-vue-i18n'
// import { createRouter, createWebHashHistory } from "vue-router";
import Persons from "../vue/components/Persons.vue";

// const routes = [
//     {path: '/vue', component: Competitions},
//     {path: '/vue/persons', component: Persons},
// ]

// const router = createRouter({
//     history: createWebHashHistory(),
//     routes, // short for `routes: routes`
// })

// let app = createApp(App);
// app.use(router);
// app.mount("#app");

let app = createApp({});
app.component('persons', Persons)
app.use(i18nVue, {
    resolve: lang => import(`../lang/${lang}.json`),
})
app.mount("#app");

require('./scripts');
