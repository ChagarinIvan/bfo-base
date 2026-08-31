<script setup lang="ts">
import { reactive, ref } from 'vue'
import type { AxiosError } from 'axios'
import Card from 'primevue/card'
import { useToast } from 'primevue/usetoast'
import { useRouter } from 'vue-router'
import { api } from '../../api/client'
import { t } from '../../i18n'
import { applyFieldErrors } from './competitionModels'
import CompetitionForm from './CompetitionForm.vue'
import type {
    ApiErrorResponse,
    CreateCompetitionRequest,
} from '../../api/types'

const router = useRouter()
const toast = useToast()
const error = ref('')
const pending = ref(false)
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

async function submit(form: CreateCompetitionRequest): Promise<void> {
    error.value = ''
    pending.value = true
    Object.keys(fieldErrors).forEach((field) => delete fieldErrors[field])

    try {
        await api.post('/competitions', form)
        toast.add({
            severity: 'success',
            summary: t('spa.competition.create.success'),
            life: 3000,
        })
        await router.push('/app/competitions')
    } catch (exception: unknown) {
        if (isValidationError(exception) && exception.response) {
            applyFieldErrors(exception.response.data.errors, fieldErrors)
        }
        error.value = t('spa.competition.create.error')
    } finally {
        pending.value = false
    }
}
</script>

<template>
    <Card class="form-card">
        <template #title>{{ t('spa.competition.create.title') }}</template>
        <template #content>
            <CompetitionForm
                :errors="fieldErrors"
                :error="error"
                :pending="pending"
                :submit-label="t('spa.competition.create.submit')"
                @submit="submit"
            />
        </template>
    </Card>
</template>
