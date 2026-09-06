<script setup lang="ts">
import { reactive, ref } from 'vue'
import Card from 'primevue/card'
import { useRoute, useRouter } from 'vue-router'
import { createOrUpdatePersonPayment } from '../../api/personPayments'
import type { ApiErrorResponse, PersonPaymentRequest } from '../../api/types'
import { applyFieldErrors, isApiValidationError } from '../listingModels'
import { t } from '../../i18n'
import PersonPaymentForm from './PersonPaymentForm.vue'

const route = useRoute()
const router = useRouter()
const pending = ref(false)
const error = ref('')
const fieldErrors = reactive<Record<string, string>>({})

async function submit(value: PersonPaymentRequest): Promise<void> {
    pending.value = true
    error.value = ''
    Object.keys(fieldErrors).forEach((key) => delete fieldErrors[key])

    try {
        await createOrUpdatePersonPayment(String(route.params.personId), value)
        await router.push('/app/persons/' + route.params.personId + '/payments')
    } catch (exception: unknown) {
        if (isApiValidationError(exception)) {
            applyFieldErrors(
                (exception as { response: { data: ApiErrorResponse } }).response
                    .data.errors,
                fieldErrors,
            )
        } else {
            error.value = t('spa.person_payment.save_error')
        }
    } finally {
        pending.value = false
    }
}
</script>

<template>
    <Card class="form-card">
        <template #title>{{ t('spa.person_payment.create_title') }}</template>
        <template #content>
            <PersonPaymentForm
                :errors="fieldErrors"
                :pending="pending"
                :error="error"
                @submit="submit"
            />
        </template>
    </Card>
</template>
