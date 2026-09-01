<script setup lang="ts">
import { reactive, ref } from 'vue'
import type { AxiosError } from 'axios'
import Card from 'primevue/card'
import { useToast } from 'primevue/usetoast'
import { useRouter } from 'vue-router'
import { createClub } from '../../api/clubs'
import type { ApiErrorResponse, CreateClubRequest } from '../../api/types'
import { t, type TranslationKey } from '../../i18n'
import { applyFieldErrors } from '../listingModels'
import ClubForm from './ClubForm.vue'

const router = useRouter()
const toast = useToast()
const fieldErrors = reactive<Record<string, string>>({})
const error = ref('')
const pending = ref(false)

function isValidationError(
    exception: unknown,
): exception is AxiosError<ApiErrorResponse> {
    return (
        typeof exception === 'object' &&
        exception !== null &&
        'isAxiosError' in exception &&
        exception.isAxiosError === true &&
        [409, 422].includes((exception as AxiosError).response?.status ?? 0)
    )
}

function messageFor(exception: AxiosError<ApiErrorResponse>): string {
    const code = exception.response?.data.errors[0]?.code
    return code
        ? t(`spa.errors.${code}` as TranslationKey)
        : t('spa.club.create.error')
}

async function submit(value: CreateClubRequest): Promise<void> {
    pending.value = true
    error.value = ''
    Object.keys(fieldErrors).forEach((field) => delete fieldErrors[field])
    try {
        const club = await createClub(value)
        toast.add({
            severity: 'success',
            summary: t('spa.club.create.success'),
            life: 3000,
        })
        await router.push(`/app/clubs/${club.id}`)
    } catch (exception: unknown) {
        if (isValidationError(exception) && exception.response) {
            applyFieldErrors(exception.response.data.errors, fieldErrors)
        }
        error.value = isValidationError(exception) && exception.response
            ? messageFor(exception)
            : t('spa.club.create.error')
    } finally {
        pending.value = false
    }
}
</script>

<template>
    <Card class="form-card">
        <template #title>{{ t('spa.club.create.title') }}</template>
        <template #content>
            <ClubForm
                :errors="fieldErrors"
                :error="error"
                :pending="pending"
                :submit-label="t('spa.club.create.submit')"
                @submit="submit"
            />
        </template>
    </Card>
</template>
