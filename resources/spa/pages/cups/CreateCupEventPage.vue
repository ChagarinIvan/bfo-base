<script setup lang="ts">
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import type { AxiosError } from 'axios'
import Card from 'primevue/card'
import Message from 'primevue/message'
import type { PageState } from 'primevue/paginator'
import { useToast } from 'primevue/usetoast'
import { useRoute, useRouter } from 'vue-router'
import { createCupEvent, getCup } from '../../api/cups'
import { getCupEventOptionsPage } from '../../api/events'
import type {
    ApiErrorResponse,
    Cup,
    CupEventFormRequest,
    Event,
    PaginationHeaders,
} from '../../api/types'
import { t } from '../../i18n'
import {
    applyFieldErrors,
    debounce,
    hasTooShortNameSearch,
    normaliseNameSearch,
    paginationFromHeaders,
} from '../listingModels'
import CupEventForm from './CupEventForm.vue'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const cup = ref<Cup | null>(null)
const events = ref<Event[]>([])
const search = ref('')
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    hasNext: false,
})
const optionsLoading = ref(false)
const optionsError = ref('')
const loading = ref(true)
const pending = ref(false)
const error = ref('')
const fieldErrors = reactive<Record<string, string>>({})
let optionsRequestVersion = 0
const debouncedSearch = debounce(() => void loadOptions(1))

onBeforeUnmount(() => {
    optionsRequestVersion++
    debouncedSearch.cancel()
})

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
        await loadOptions(1)
    } catch (exception) {
        error.value = isApiError(exception, 404)
            ? t('spa.cup.form.not_found')
            : t('spa.cup_event.create.error')
    } finally {
        loading.value = false
    }
}

async function loadOptions(page: number): Promise<void> {
    if (!cup.value) return

    const version = ++optionsRequestVersion
    const name = normaliseNameSearch(search.value)
    optionsLoading.value = true
    optionsError.value = ''
    try {
        const response = await getCupEventOptionsPage({
            year: cup.value.year,
            notRelatedToCup: cup.value.id,
            sortBy: 'date',
            sort: 'desc',
            page,
            perPage: 20,
            ...(name ? { competitionOrEventName: name } : {}),
        })
        if (version !== optionsRequestVersion) return

        events.value = response.data
        pagination.value = paginationFromHeaders(response.headers)
    } catch {
        if (version !== optionsRequestVersion) return
        events.value = []
        optionsError.value = t('spa.cup_event.form.options_error')
    } finally {
        if (version === optionsRequestVersion) optionsLoading.value = false
    }
}

function onSearch(value: string): void {
    search.value = value
    debouncedSearch.cancel()
    optionsRequestVersion++
    optionsLoading.value = false
    optionsError.value = ''
    events.value = []
    pagination.value = { currentPage: 1, perPage: 20, hasNext: false }
    if (!hasTooShortNameSearch(value)) {
        optionsLoading.value = true
        debouncedSearch()
    }
}

function onPage(event: PageState): void {
    void loadOptions(event.page + 1)
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
    <Message v-else-if="!cup" severity="error" :closable="false">{{
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
                remote-search
                :search="search"
                :pagination="pagination"
                :options-loading="optionsLoading"
                :options-error="optionsError"
                @update:search="onSearch"
                @page="onPage"
                @submit="submit"
        /></template>
    </Card>
</template>
