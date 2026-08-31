<script setup lang="ts">
import { useAuthStore } from '../stores/auth'
import { t } from '../i18n'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import brandIconUrl from '../assets/icon.svg'
import {
    authenticatedAccountNavigation,
    authenticatedCompetitionNavigation,
    competitionNavigation,
    personsNavigation,
} from './navigationModels'

const auth = useAuthStore()
const router = useRouter()
const toast = useToast()

async function logout(): Promise<void> {
    try {
        await auth.logout()
    } catch {
        toast.add({
            severity: 'error',
            summary: t('spa.nav.logout_error'),
            life: 5000,
        })
    } finally {
        await router.push('/app/competitions')
    }
}
</script>

<template>
    <header class="app-header">
        <nav class="app-navbar">
            <RouterLink class="app-brand" to="/app/competitions">
                <img
                    class="app-brand-mark"
                    :src="brandIconUrl"
                    alt=""
                    aria-hidden="true"
                />
                <span>{{ t('spa.nav.brand') }}</span>
            </RouterLink>
            <div class="app-nav-links">
                <details class="app-nav-menu">
                    <summary class="app-nav-link">
                        <i class="pi pi-trophy" />
                        {{ t('spa.nav.competitions') }}
                        <i class="pi pi-angle-down app-nav-menu-chevron" />
                    </summary>
                    <div class="app-nav-dropdown">
                        <template
                            v-for="item in competitionNavigation"
                            :key="item.href"
                        >
                            <RouterLink
                                v-if="item.spa"
                                class="app-nav-dropdown-link"
                                :to="item.href"
                            >
                                {{ t(item.label) }}
                            </RouterLink>
                            <a
                                v-else
                                class="app-nav-dropdown-link"
                                :href="item.href"
                            >
                                {{ t(item.label) }}
                            </a>
                        </template>
                        <template v-if="auth.isAuthenticated">
                            <a
                                v-for="item in authenticatedCompetitionNavigation"
                                :key="item.href"
                                class="app-nav-dropdown-link"
                                :href="item.href"
                            >
                                {{ t(item.label) }}
                            </a>
                        </template>
                    </div>
                </details>
                <details class="app-nav-menu">
                    <summary class="app-nav-link">
                        <i class="pi pi-users" />
                        {{ t('spa.nav.persons') }}
                        <i class="pi pi-angle-down app-nav-menu-chevron" />
                    </summary>
                    <div class="app-nav-dropdown">
                        <a
                            v-for="item in personsNavigation"
                            :key="item.href"
                            class="app-nav-dropdown-link"
                            :href="item.href"
                        >
                            {{ t(item.label) }}
                        </a>
                    </div>
                </details>
            </div>
            <div class="app-nav-auth">
                <RouterLink
                    v-if="!auth.isAuthenticated"
                    class="app-nav-link app-login-link"
                    to="/app/login"
                >
                    <i class="pi pi-sign-in" /> {{ t('spa.nav.login') }}
                </RouterLink>
                <template v-else>
                    <a
                        v-for="item in authenticatedAccountNavigation"
                        :key="item.href"
                        class="app-nav-link"
                        :href="item.href"
                    >
                        <i class="pi pi-user-plus" /> {{ t(item.label) }}
                    </a>
                    <button
                        class="app-logout-button"
                        type="button"
                        @click="logout"
                    >
                        <i class="pi pi-sign-out" /> {{ t('spa.nav.logout') }}
                    </button>
                </template>
            </div>
        </nav>
    </header>
    <main class="app-container">
        <slot />
    </main>
</template>
