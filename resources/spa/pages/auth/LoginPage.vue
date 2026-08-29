<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { t } from '../../i18n'

const email = ref('')
const password = ref('')
const error = ref('')
const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

async function submit(): Promise<void> {
  error.value = ''
  try {
    await auth.login(email.value, password.value)
    await router.push(String(route.query.return || '/app/competitions'))
  } catch {
    error.value = t('spa.login.error')
  }
}
</script>

<template>
  <h1>{{ t('spa.login.title') }}</h1>
  <form @submit.prevent="submit">
    <input v-model="email" type="email" required :placeholder="t('spa.login.email')">
    <input v-model="password" type="password" required :placeholder="t('spa.login.password')">
    <button type="submit">{{ t('spa.login.submit') }}</button>
    <p v-if="error">{{ error }}</p>
  </form>
</template>
