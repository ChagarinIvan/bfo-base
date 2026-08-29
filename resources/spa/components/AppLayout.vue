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
        <nav>
            <RouterLink to="/app/competitions">{{
                t('spa.nav.competitions')
            }}</RouterLink>
            <RouterLink
                v-if="auth.isAuthenticated"
                to="/app/competitions/create"
                >{{ t('spa.nav.create') }}</RouterLink
            >
            <RouterLink v-if="!auth.isAuthenticated" to="/app/login">{{
                t('spa.nav.login')
            }}</RouterLink>
            <button v-else type="button" @click="logout">
                {{ t('spa.nav.logout') }}
            </button>
        </nav>
    </header>
    <main class="app-container">
        <slot />
    </main>
</template>
