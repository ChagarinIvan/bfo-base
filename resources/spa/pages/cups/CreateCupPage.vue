<script setup lang="ts">
import { reactive, ref } from 'vue'
import type { AxiosError } from 'axios'
import Card from 'primevue/card'
import { useToast } from 'primevue/usetoast'
import { useRouter } from 'vue-router'
import { createCup } from '../../api/cups'
import type { ApiErrorResponse, CreateCupRequest } from '../../api/types'
import { t } from '../../i18n'
import { applyFieldErrors } from '../listingModels'
import CupForm from './CupForm.vue'

const router = useRouter()
const toast = useToast()
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

async function submit(value: CreateCupRequest): Promise<void> {
    pending.value = true
    error.value = ''
    Object.keys(fieldErrors).forEach((field) => delete fieldErrors[field])
    try {
        await createCup(value)
        toast.add({
            severity: 'success',
            summary: t('spa.cup.create.success'),
            life: 3000,
        })
        await router.push('/app/cups')
    } catch (exception: unknown) {
        if (isValidationError(exception) && exception.response)
            applyFieldErrors(exception.response.data.errors, fieldErrors)
        error.value = t('spa.cup.create.error')
    } finally {
        pending.value = false
    }
}
</script>

<template>
    <Card class="form-card">
        <template #title>{{ t('spa.cup.create.title') }}</template>
        <template #content
            ><CupForm
                :errors="fieldErrors"
                :error="error"
                :pending="pending"
                :submit-label="t('spa.cup.create.submit')"
                @submit="submit"
        /></template>
    </Card>
</template>
