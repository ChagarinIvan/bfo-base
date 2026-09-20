<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import type { AxiosError } from 'axios'
import Card from 'primevue/card'
import Message from 'primevue/message'
import { useToast } from 'primevue/usetoast'
import { useRoute, useRouter } from 'vue-router'
import { getCup, getCupEvent, updateCupEvent } from '../../api/cups'
import { getCupEventOptions, getEventsByIds } from '../../api/events'
import type {
    ApiErrorResponse,
    Cup,
    CupEvent,
    CupEventFormRequest,
    Event,
} from '../../api/types'
import { t } from '../../i18n'
import { applyFieldErrors } from '../listingModels'
import CupEventForm from './CupEventForm.vue'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const cup = ref<Cup | null>(null)
const cupEvent = ref<CupEvent | null>(null)
const events = ref<Event[]>([])
const loading = ref(true)
const pending = ref(false)
const error = ref('')
const fieldErrors = reactive<Record<string, string>>({})
const initialValue = computed(() =>
    cupEvent.value
        ? {
              eventId: Number(cupEvent.value.eventId),
              points: Number(cupEvent.value.points),
          }
        : {},
)

function isApiError(
    exception: unknown,
    status: number,
): exception is AxiosError<ApiErrorResponse> {
    return (
        typeof exception === 'object' &&
        exception !== null &&
        'isAxiosError' in exception &&
        exception.isAxiosError === true &&
        (exception as AxiosError).response?.status === status
    )
}

async function load(): Promise<void> {
    try {
        const cupId = String(route.params.cupId)
        cup.value = await getCup(cupId)
        cupEvent.value = await getCupEvent(String(route.params.cupEventId))
        const [availableEvents, selectedEvents] = await Promise.all([
            getCupEventOptions({
                year: cup.value.year,
                notRelatedToCup: cupId,
                perPage: 100,
            }),
            getEventsByIds([cupEvent.value.eventId]),
        ])
        events.value = [
            ...selectedEvents,
            ...availableEvents.filter(
                (event) => event.id !== cupEvent.value?.eventId,
            ),
        ]
    } catch (exception) {
        error.value = isApiError(exception, 404)
            ? t('spa.cup_event.form.not_found')
            : t('spa.cup_event.edit.error')
    } finally {
        loading.value = false
    }
}

async function submit(value: CupEventFormRequest): Promise<void> {
    pending.value = true
    error.value = ''
    Object.keys(fieldErrors).forEach((field) => delete fieldErrors[field])
    try {
        await updateCupEvent(String(route.params.cupEventId), value)
        toast.add({
            severity: 'success',
            summary: t('spa.cup_event.edit.success'),
            life: 3000,
        })
        await router.push(`/app/cups/${route.params.cupId}`)
    } catch (exception) {
        if (isApiError(exception, 422) && exception.response)
            applyFieldErrors(exception.response.data.errors, fieldErrors)
        error.value = t('spa.cup_event.edit.error')
    } finally {
        pending.value = false
    }
}

onMounted(load)
</script>

<template>
    <Message v-if="loading" severity="info" :closable="false">{{
        t('spa.cups.loading')
    }}</Message>
    <Message
        v-else-if="error || !cupEvent"
        severity="error"
        :closable="false"
        >{{ error }}</Message
    >
    <Card v-else class="form-card">
        <template #title>{{ t('spa.cup_event.edit.title') }}</template>
        <template #content
            ><CupEventForm
                :events="events"
                :initial-value="initialValue"
                :errors="fieldErrors"
                :error="error"
                :pending="pending"
                :submit-label="t('spa.cup_event.edit.submit')"
                @submit="submit"
        /></template>
    </Card>
</template>
