<script setup lang="ts">
import { reactive, ref } from 'vue'
import type { AxiosError } from 'axios'
import { useToast } from 'primevue/usetoast'
import { useRouter } from 'vue-router'
import { api } from '../../api/client'
import { t } from '../../i18n'
import type {
    ApiErrorResponse,
    ApiResponse,
    CreateCompetitionRequest,
} from '../../api/types'

const router = useRouter()
const toast = useToast()
const error = ref('')
const fieldErrors = reactive<Record<string, string>>({})
const form = reactive<CreateCompetitionRequest>({
    name: '',
    description: '',
    from: '',
    to: '',
    mass: false,
})

function isValidationError(
    exception: unknown,
): exception is AxiosError<ApiErrorResponse> {
    return (
        typeof exception === 'object' &&
        exception !== null &&
        'isAxiosError' in exception &&
        exception.isAxiosError === true &&
        'response' in exception &&
        exception.response !== undefined &&
        exception.response !== null &&
        typeof exception.response === 'object' &&
        'status' in exception.response &&
        exception.response.status === 422
    )
}

async function submit(): Promise<void> {
    error.value = ''
    Object.keys(fieldErrors).forEach((field) => delete fieldErrors[field])
    try {
        await api.post<ApiResponse<unknown>>('/competitions', form)
        toast.add({
            severity: 'success',
            summary: t('spa.competition.create.success'),
            life: 3000,
        })
        await router.push('/app/competitions')
    } catch (exception: unknown) {
        if (isValidationError(exception)) {
            const response = exception.response
            if (!response) return

            for (const item of response.data.errors) {
                if (item.field) fieldErrors[item.field] = item.message
            }
        }
        error.value = t('spa.competition.create.error')
    }
}
</script>

<template>
    <h1>{{ t('spa.competition.create.title') }}</h1>
    <form @submit.prevent="submit">
        <input
            v-model="form.name"
            required
            :placeholder="t('spa.competition.create.name')"
        />
        <p v-if="fieldErrors.name">{{ fieldErrors.name }}</p>
        <textarea
            v-model="form.description"
            required
            :placeholder="t('spa.competition.create.description')"
        />
        <p v-if="fieldErrors.description">{{ fieldErrors.description }}</p>
        <input
            v-model="form.from"
            required
            type="date"
            :aria-label="t('spa.competition.create.from')"
        />
        <p v-if="fieldErrors.from">{{ fieldErrors.from }}</p>
        <input
            v-model="form.to"
            required
            type="date"
            :aria-label="t('spa.competition.create.to')"
        />
        <p v-if="fieldErrors.to">{{ fieldErrors.to }}</p>
        <label
            ><input v-model="form.mass" type="checkbox" />
            {{ t('spa.competition.create.mass') }}</label
        >
        <button type="submit">{{ t('spa.competition.create.submit') }}</button>
        <p v-if="error">{{ error }}</p>
    </form>
</template>
