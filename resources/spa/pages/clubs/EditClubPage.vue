<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import type { AxiosError } from 'axios'
import Card from 'primevue/card'
import Message from 'primevue/message'
import { useToast } from 'primevue/usetoast'
import { useRoute, useRouter } from 'vue-router'
import { getClub, updateClub } from '../../api/clubs'
import type { ApiErrorResponse, Club, CreateClubRequest } from '../../api/types'
import { t, type TranslationKey } from '../../i18n'
import { applyFieldErrors } from '../listingModels'
import ClubForm from './ClubForm.vue'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const club = ref<Club | null>(null)
const fieldErrors = reactive<Record<string, string>>({})
const error = ref('')
const loading = ref(true)
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
        : t('spa.club.edit.error')
}

async function load(): Promise<void> {
    try {
        club.value = await getClub(String(route.params.id))
    } catch {
        error.value = t('spa.club.details.not_found')
    } finally {
        loading.value = false
    }
}

async function submit(value: CreateClubRequest): Promise<void> {
    pending.value = true
    error.value = ''
    Object.keys(fieldErrors).forEach((field) => delete fieldErrors[field])
    try {
        const updated = await updateClub(String(route.params.id), value)
        toast.add({
            severity: 'success',
            summary: t('spa.club.edit.success'),
            life: 3000,
        })
        await router.push(`/app/clubs/${updated.id}`)
    } catch (exception: unknown) {
        if (isValidationError(exception) && exception.response) {
            applyFieldErrors(exception.response.data.errors, fieldErrors)
        }
        error.value =
            isValidationError(exception) && exception.response
                ? messageFor(exception)
                : t('spa.club.edit.error')
    } finally {
        pending.value = false
    }
}

onMounted(() => void load())
</script>

<template>
    <Message v-if="loading" severity="info" :closable="false">{{
        t('spa.clubs.loading')
    }}</Message>
    <Message v-else-if="error && !club" severity="error" :closable="false">{{
        error
    }}</Message>
    <Card v-else-if="club" class="form-card">
        <template #title>{{ t('spa.club.edit.title') }}</template>
        <template #content>
            <ClubForm
                :initial-value="club"
                :errors="fieldErrors"
                :error="error"
                :pending="pending"
                :submit-label="t('spa.club.edit.submit')"
                @submit="submit"
            />
        </template>
    </Card>
</template>
