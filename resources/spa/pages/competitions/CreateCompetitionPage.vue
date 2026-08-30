<script setup lang="ts">
import { reactive, ref } from 'vue'
import type { AxiosError } from 'axios'
import { useToast } from 'primevue/usetoast'
import { useRouter } from 'vue-router'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Checkbox from 'primevue/checkbox'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import Textarea from 'primevue/textarea'
import { api } from '../../api/client'
import { t } from '../../i18n'
import { applyFieldErrors, isDateRangeValid } from './competitionModels'
import type {
    ApiErrorResponse,
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

    if (!isDateRangeValid(form.from, form.to)) {
        fieldErrors.to = t('spa.competition.create.date_order')
        error.value = t('spa.competition.create.error')
        return
    }

    try {
        await api.post('/competitions', form)
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

            applyFieldErrors(response.data.errors, fieldErrors)
        }
        error.value = t('spa.competition.create.error')
    }
}
</script>

<template>
    <Card class="form-card">
        <template #title>{{ t('spa.competition.create.title') }}</template>
        <template #content>
            <form class="spa-form" @submit.prevent="submit">
                <div class="form-field">
                    <label for="competition-name">{{
                        t('spa.competition.create.name')
                    }}</label>
                    <InputText
                        id="competition-name"
                        v-model="form.name"
                        required
                    />
                    <small v-if="fieldErrors.name" class="field-error">{{
                        fieldErrors.name
                    }}</small>
                </div>
                <div class="form-field">
                    <label for="competition-description">{{
                        t('spa.competition.create.description')
                    }}</label>
                    <Textarea
                        id="competition-description"
                        v-model="form.description"
                        required
                        rows="4"
                    />
                    <small v-if="fieldErrors.description" class="field-error">{{
                        fieldErrors.description
                    }}</small>
                </div>
                <div class="form-grid">
                    <div class="form-field">
                        <label for="competition-from">{{
                            t('spa.competition.create.from')
                        }}</label>
                        <InputText
                            id="competition-from"
                            v-model="form.from"
                            required
                            type="date"
                        />
                        <small v-if="fieldErrors.from" class="field-error">{{
                            fieldErrors.from
                        }}</small>
                    </div>
                    <div class="form-field">
                        <label for="competition-to">{{
                            t('spa.competition.create.to')
                        }}</label>
                        <InputText
                            id="competition-to"
                            v-model="form.to"
                            required
                            type="date"
                        />
                        <small v-if="fieldErrors.to" class="field-error">{{
                            fieldErrors.to
                        }}</small>
                    </div>
                </div>
                <div class="form-checkbox">
                    <Checkbox
                        v-model="form.mass"
                        input-id="competition-mass"
                        binary
                    />
                    <label for="competition-mass">{{
                        t('spa.competition.create.mass')
                    }}</label>
                </div>
                <Button
                    type="submit"
                    :label="t('spa.competition.create.submit')"
                    severity="success"
                />
                <Message v-if="error" severity="error" :closable="false">{{
                    error
                }}</Message>
            </form>
        </template>
    </Card>
</template>
