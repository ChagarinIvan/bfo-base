<script setup lang="ts">
import { useAuthStore } from '../stores/auth'
import { t } from '../i18n'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'

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
                <span class="app-brand-mark" aria-hidden="true">
                    <i class="pi pi-database" />
                    <i class="pi pi-map-marker app-brand-checkpoint" />
                </span>
                <span>{{ t('spa.nav.brand') }}</span>
            </RouterLink>
            <div class="app-nav-links">
                <RouterLink class="app-nav-link" to="/app/competitions">
                    <i class="pi pi-trophy" /> {{ t('spa.nav.competitions') }}
                </RouterLink>
                <RouterLink
                    v-if="auth.isAuthenticated"
                    class="app-nav-link"
                    to="/app/competitions/create"
                >
                    <i class="pi pi-plus" /> {{ t('spa.nav.create') }}
                </RouterLink>
            </div>
            <div class="app-nav-auth">
                <RouterLink
                    v-if="!auth.isAuthenticated"
                    class="app-nav-link app-login-link"
                    to="/app/login"
                >
                    <i class="pi pi-sign-in" /> {{ t('spa.nav.login') }}
                </RouterLink>
                <button
                    v-else
                    class="app-logout-button"
                    type="button"
                    @click="logout"
                >
                    <i class="pi pi-sign-out" /> {{ t('spa.nav.logout') }}
                </button>
            </div>
        </nav>
    </header>
    <main class="app-container">
        <slot />
    </main>
</template>
