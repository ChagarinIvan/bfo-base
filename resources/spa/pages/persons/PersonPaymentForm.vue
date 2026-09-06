<script setup lang="ts">
import { reactive, watch } from 'vue'
import Button from 'primevue/button'
import Message from 'primevue/message'
import DateFilter from '../../components/DateFilter.vue'
import { t } from '../../i18n'
import type { PersonPaymentRequest } from '../../api/types'

const props = withDefaults(
    defineProps<{
        initialValue?: Partial<PersonPaymentRequest>
        errors?: Record<string, string>
        pending?: boolean
        error?: string
    }>(),
    { initialValue: () => ({}), errors: () => ({}), pending: false, error: '' },
)
const emit = defineEmits<{ submit: [value: PersonPaymentRequest] }>()
const form = reactive<PersonPaymentRequest>({ date: '' })

watch(
    () => props.initialValue,
    (value) => {
        form.date = value?.date ?? ''
    },
    { immediate: true },
)

function submit(): void {
    emit('submit', { date: form.date })
}
</script>

<template>
    <form class="spa-form" @submit.prevent="submit">
        <DateFilter
            v-model="form.date"
            class="form-field"
            input-id="person-payment-date"
            :label="t('spa.person_payment.date')"
            :error="errors.date"
            required
            :disabled="pending"
        />
        <Button
            type="submit"
            :label="t('spa.person_payment.save')"
            severity="success"
            :loading="pending"
        />
        <Message v-if="error" severity="error" :closable="false">{{
            error
        }}</Message>
    </form>
</template>
