<script setup lang="ts">
import { reactive, ref } from 'vue'
import type { AxiosError } from 'axios'
import Card from 'primevue/card'
import { useToast } from 'primevue/usetoast'
import { useRoute, useRouter } from 'vue-router'
import { createEvent } from '../../api/events'
import type { ApiErrorResponse, EventFormRequest } from '../../api/types'
import { t } from '../../i18n'
import { applyFieldErrors } from '../competitions/competitionModels'
import { eventErrorMessage } from './eventModels'
import EventForm from './EventForm.vue'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const pending = ref(false)
const error = ref('')
const fieldErrors = reactive<Record<string, string>>({})

async function submit(form: EventFormRequest): Promise<void> {
    pending.value = true
    error.value = ''
    Object.keys(fieldErrors).forEach((field) => delete fieldErrors[field])
    try {
        const event = await createEvent(
            String(route.params.competitionId),
            form,
        )
        toast.add({
            severity: 'success',
            summary: t('spa.event.create.success'),
            life: 3000,
        })
        await router.push(`/app/events/${event.id}`)
    } catch (exception: unknown) {
        const response = (exception as AxiosError<ApiErrorResponse>).response
        if (response?.status === 422)
            applyFieldErrors(response.data.errors, fieldErrors)
        error.value = eventErrorMessage(exception, 'spa.event.create.error')
    } finally {
        pending.value = false
    }
}
</script>

<template>
    <Card class="form-card">
        <template #title>{{ t('spa.event.create.title') }}</template>
        <template #content
            ><EventForm
                :submit-label="t('spa.event.create.submit')"
                :pending="pending"
                :errors="fieldErrors"
                :error="error"
                source-required
                @submit="submit"
        /></template>
    </Card>
</template>
