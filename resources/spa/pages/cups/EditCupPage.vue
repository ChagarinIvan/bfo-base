<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import type { AxiosError } from 'axios'
import Card from 'primevue/card'
import Message from 'primevue/message'
import { useToast } from 'primevue/usetoast'
import { useRoute, useRouter } from 'vue-router'
import { getCup, updateCup } from '../../api/cups'
import type { ApiErrorResponse, Cup, UpdateCupRequest } from '../../api/types'
import { t } from '../../i18n'
import { applyFieldErrors } from '../listingModels'
import CupForm from './CupForm.vue'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const cup = ref<Cup | null>(null)
const loading = ref(true)
const pending = ref(false)
const error = ref('')
const fieldErrors = reactive<Record<string, string>>({})

function isValidationError(
    exception: unknown,
): exception is AxiosError<ApiErrorResponse> {
    return (
        typeof exception === 'object' &&
        exception !== null &&
        'isAxiosError' in exception &&
        exception.isAxiosError === true &&
        (exception as AxiosError).response?.status === 422
    )
}

function isNotFound(exception: unknown): boolean {
    return (
        typeof exception === 'object' &&
        exception !== null &&
        'isAxiosError' in exception &&
        exception.isAxiosError === true &&
        (exception as AxiosError).response?.status === 404
    )
}

async function load(): Promise<void> {
    try {
        cup.value = await getCup(String(route.params.id))
    } catch (exception: unknown) {
        error.value = isNotFound(exception)
            ? t('spa.cup.form.not_found')
            : t('spa.cup.edit.load_error')
    } finally {
        loading.value = false
    }
}

async function submit(value: UpdateCupRequest): Promise<void> {
    pending.value = true
    error.value = ''
    Object.keys(fieldErrors).forEach((field) => delete fieldErrors[field])
    try {
        await updateCup(String(route.params.id), value)
        toast.add({
            severity: 'success',
            summary: t('spa.cup.edit.success'),
            life: 3000,
        })
        await router.push('/app/cups')
    } catch (exception: unknown) {
        if (isValidationError(exception) && exception.response) {
            applyFieldErrors(exception.response.data.errors, fieldErrors)
        }
        error.value = t('spa.cup.edit.error')
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
    <Message v-else-if="error && !cup" severity="error" :closable="false">{{
        error
    }}</Message>
    <Card v-else-if="cup" class="form-card">
        <template #title>{{ t('spa.cup.edit.title') }}</template>
        <template #content
            ><CupForm
                :initial-value="cup"
                :errors="fieldErrors"
                :error="error"
                :pending="pending"
                :submit-label="t('spa.cup.edit.submit')"
                @submit="submit"
        /></template>
    </Card>
</template>
