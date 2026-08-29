<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '../../api/client'
import { t } from '../../i18n'

const router = useRouter()
const error = ref('')
const form = reactive({ name: '', description: '', from: '', to: '', mass: false })

async function submit(): Promise<void> {
  error.value = ''
  try {
    await api.post('/competitions', form)
    await router.push('/app/competitions')
  } catch {
    error.value = t('spa.competition.create.error')
  }
}
</script>

<template>
  <h1>{{ t('spa.competition.create.title') }}</h1>
  <form @submit.prevent="submit">
    <input v-model="form.name" required :placeholder="t('spa.competition.create.name')">
    <textarea v-model="form.description" required :placeholder="t('spa.competition.create.description')" />
    <input v-model="form.from" required type="date" :aria-label="t('spa.competition.create.from')">
    <input v-model="form.to" required type="date" :aria-label="t('spa.competition.create.to')">
    <label><input v-model="form.mass" type="checkbox"> {{ t('spa.competition.create.mass') }}</label>
    <button type="submit">{{ t('spa.competition.create.submit') }}</button>
    <p v-if="error">{{ error }}</p>
  </form>
</template>
