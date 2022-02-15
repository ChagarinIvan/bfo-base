require('./bootstrap');

import {createApp} from 'vue'
import {i18nVue} from 'laravel-vue-i18n'
import {createRouter, createWebHashHistory} from "vue-router";
import Persons from "../vue/components/person/Persons.vue";
import PersonEditForm from "../vue/components/person/PersonForm.vue";
import PersonCreateForm from "../vue/components/person/PersonCreateForm.vue";

const routes = [
    // {path: '/vue', component: Competitions},
    {path: '/', component: Persons},
    {path: '/edit/:id', component: PersonEditForm},
    {path: '/create', component: PersonCreateForm},
]

const router = createRouter({
    history: createWebHashHistory(),
    routes, // short for `routes: routes`
})

let app = createApp({});
app.use(router);
app.component('persons', Persons)
app.use(i18nVue, {
    resolve: lang => import(`../lang/${lang}.json`),
})
app.mount("#app");

require('./scripts');
