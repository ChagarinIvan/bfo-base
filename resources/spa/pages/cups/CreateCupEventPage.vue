<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import type { AxiosError } from 'axios'
import Card from 'primevue/card'
import Message from 'primevue/message'
import { useToast } from 'primevue/usetoast'
import { useRoute, useRouter } from 'vue-router'
import { createCupEvent, getCup } from '../../api/cups'
import { getCupEventOptions } from '../../api/events'
import type {
    ApiErrorResponse,
    Cup,
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
const events = ref<Event[]>([])
const loading = ref(true)
const pending = ref(false)
const error = ref('')
const fieldErrors = reactive<Record<string, string>>({})

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
        cup.value = await getCup(String(route.params.cupId))
        events.value = await getCupEventOptions({
            year: cup.value.year,
            notRelatedToCup: cup.value.id,
            perPage: 100,
        })
    } catch (exception) {
        error.value = isApiError(exception, 404)
            ? t('spa.cup.form.not_found')
            : t('spa.cup_event.create.error')
    } finally {
        loading.value = false
    }
}

async function submit(value: CupEventFormRequest): Promise<void> {
    pending.value = true
    error.value = ''
    Object.keys(fieldErrors).forEach((field) => delete fieldErrors[field])
    try {
        await createCupEvent({
            ...value,
            cupId: Number(route.params.cupId),
        })
        toast.add({
            severity: 'success',
            summary: t('spa.cup_event.create.success'),
            life: 3000,
        })
        await router.push(`/app/cups/${route.params.cupId}`)
    } catch (exception) {
        if (isApiError(exception, 422) && exception.response)
            applyFieldErrors(exception.response.data.errors, fieldErrors)
        error.value = t('spa.cup_event.create.error')
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
    <Message v-else-if="error || !cup" severity="error" :closable="false">{{
        error
    }}</Message>
    <Card v-else class="form-card">
        <template #title>{{ t('spa.cup_event.create.title') }}</template>
        <template #content
            ><CupEventForm
                :events="events"
                :errors="fieldErrors"
                :error="error"
                :pending="pending"
                :submit-label="t('spa.cup_event.create.submit')"
                @submit="submit"
        /></template>
    </Card>
</template>
