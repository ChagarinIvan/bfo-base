<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import type { AxiosError } from 'axios'
import Card from 'primevue/card'
import Message from 'primevue/message'
import { useToast } from 'primevue/usetoast'
import { useRoute, useRouter } from 'vue-router'
import { getEvent, updateEvent } from '../../api/events'
import type { ApiErrorResponse, Event, EventFormRequest } from '../../api/types'
import { applyFieldErrors } from '../competitions/competitionModels'
import EventForm from './EventForm.vue'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const event = ref<Event | null>(null)
const loading = ref(true)
const pending = ref(false)
const error = ref('')
const fieldErrors = reactive<Record<string, string>>({})

async function load(): Promise<void> {
    try {
        event.value = await getEvent(String(route.params.eventId))
    } catch {
        error.value = 'Не атрымалася загрузіць этап.'
    } finally {
        loading.value = false
    }
}

async function submit(form: EventFormRequest): Promise<void> {
    pending.value = true
    error.value = ''
    Object.keys(fieldErrors).forEach((field) => delete fieldErrors[field])
    try {
        const updated = await updateEvent(String(route.params.eventId), form)
        toast.add({ severity: 'success', summary: 'Этап зменены.', life: 3000 })
        await router.push(`/app/events/${updated.id}`)
    } catch (exception: unknown) {
        const response = (exception as AxiosError<ApiErrorResponse>).response
        if (response?.status === 422)
            applyFieldErrors(response.data.errors, fieldErrors)
        error.value = 'Не атрымалася змяніць этап.'
    } finally {
        pending.value = false
    }
}

onMounted(load)
</script>

<template>
    <Message v-if="loading" severity="info" :closable="false"
        >Загрузка этапу…</Message
    >
    <Message v-else-if="error && !event" severity="error" :closable="false">{{
        error
    }}</Message>
    <Card v-else-if="event" class="form-card">
        <template #title>Рэдагаванне этапа</template>
        <template #content
            ><EventForm
                :initial-value="event"
                submit-label="Захаваць"
                :pending="pending"
                :errors="fieldErrors"
                :error="error"
                @submit="submit"
        /></template>
    </Card>
</template>
