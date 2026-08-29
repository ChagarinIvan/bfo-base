<script setup lang="ts">
import { useAuthStore } from '../stores/auth'
import { t } from '../i18n'

const auth = useAuthStore()
</script>

<template>
  <header class="app-header">
    <nav>
      <RouterLink to="/app/competitions">{{ t('spa.nav.competitions') }}</RouterLink>
      <RouterLink v-if="auth.isAuthenticated" to="/app/competitions/create">{{ t('spa.nav.create') }}</RouterLink>
      <RouterLink v-if="!auth.isAuthenticated" to="/app/login">{{ t('spa.nav.login') }}</RouterLink>
      <button v-else type="button" @click="auth.logout">{{ t('spa.nav.logout') }}</button>
    </nav>
  </header>
  <main class="app-container">
    <slot />
  </main>
</template>
