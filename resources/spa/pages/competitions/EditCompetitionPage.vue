<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import type { AxiosError } from 'axios'
import Card from 'primevue/card'
import Message from 'primevue/message'
import { useToast } from 'primevue/usetoast'
import { useRoute, useRouter } from 'vue-router'
import { getCompetition, updateCompetition } from '../../api/competitions'
import { t } from '../../i18n'
import { applyFieldErrors } from './competitionModels'
import CompetitionForm from './CompetitionForm.vue'
import type {
    ApiErrorResponse,
    Competition,
    UpdateCompetitionRequest,
} from '../../api/types'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const competition = ref<Competition | null>(null)
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

async function load(): Promise<void> {
    loading.value = true
    error.value = ''
    try {
        competition.value = await getCompetition(String(route.params.id))
    } catch {
        error.value = t('spa.competition.details.not_found')
    } finally {
        loading.value = false
    }
}

async function submit(form: UpdateCompetitionRequest): Promise<void> {
    pending.value = true
    error.value = ''
    Object.keys(fieldErrors).forEach((field) => delete fieldErrors[field])

    try {
        const updated = await updateCompetition(String(route.params.id), form)
        toast.add({
            severity: 'success',
            summary: t('spa.competition.edit.success'),
            life: 3000,
        })
        await router.push(`/app/competitions/${updated.id}`)
    } catch (exception: unknown) {
        if (isValidationError(exception) && exception.response) {
            applyFieldErrors(exception.response.data.errors, fieldErrors)
        }
        error.value = t('spa.competition.edit.error')
    } finally {
        pending.value = false
    }
}

onMounted(load)
</script>

<template>
    <Message v-if="loading" severity="info" :closable="false">
        {{ t('spa.competitions.loading') }}
    </Message>
    <Message
        v-else-if="error && !competition"
        severity="error"
        :closable="false"
    >
        {{ error }}
    </Message>
    <Card v-else-if="competition" class="form-card">
        <template #title>{{ t('spa.competition.edit.title') }}</template>
        <template #content>
            <CompetitionForm
                :initial-value="competition"
                :errors="fieldErrors"
                :error="error"
                :pending="pending"
                :submit-label="t('spa.competition.edit.submit')"
                @submit="submit"
            />
        </template>
    </Card>
</template>
