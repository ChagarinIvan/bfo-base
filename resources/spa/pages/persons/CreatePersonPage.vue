<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import Card from 'primevue/card'
import Message from 'primevue/message'
import { useRouter } from 'vue-router'
import { getClubOptions } from '../../api/clubs'
import { createPerson } from '../../api/persons'
import type { ClubOption, PersonFormRequest } from '../../api/types'
import { t } from '../../i18n'
import { applyFieldErrors, isApiValidationError } from '../listingModels'
import PersonForm from './PersonForm.vue'
const router = useRouter()
const clubs = ref<ClubOption[]>([])
const errors = reactive<Record<string, string>>({})
const error = ref('')
const loading = ref(true)
const pending = ref(false)

async function load(): Promise<void> {
    try {
        clubs.value = await getClubOptions()
    } catch {
        error.value = t('spa.person.error')
    } finally {
        loading.value = false
    }
}

onMounted(() => void load())
async function submit(value: PersonFormRequest): Promise<void> {
    pending.value = true
    error.value = ''
    Object.keys(errors).forEach((key) => delete errors[key])
    try {
        const person = await createPerson(value)
        await router.push(`/app/persons/${person.id}`)
    } catch (exception: unknown) {
        if (isApiValidationError(exception)) {
            applyFieldErrors(exception.response.data.errors, errors)
        } else {
            error.value = t('spa.person.error')
        }
    } finally {
        pending.value = false
    }
}
</script>
<template>
    <Message
        v-if="loading || error"
        :severity="error ? 'error' : 'info'"
        :closable="false"
        >{{ error || t('spa.person.loading') }}</Message
    >
    <Card v-else class="form-card"
        ><template #title>{{ t('spa.person.create') }}</template
        ><template #content
            ><PersonForm
                :clubs="clubs"
                :errors="errors"
                :pending="pending"
                @submit="submit" /></template
    ></Card>
</template>
