<script setup lang="ts">
import { reactive, watch } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import { t } from '../../i18n'
import type { CreateClubRequest } from '../../api/types'

const props = withDefaults(
    defineProps<{
        initialValue?: Partial<CreateClubRequest>
        errors?: Record<string, string>
        submitLabel: string
        pending?: boolean
        error?: string
    }>(),
    { initialValue: () => ({}), errors: () => ({}), pending: false, error: '' },
)
const emit = defineEmits<{ submit: [value: CreateClubRequest] }>()
const form = reactive<CreateClubRequest>({ name: '' })

watch(
    () => props.initialValue,
    (value) => {
        form.name = value?.name ?? ''
    },
    { immediate: true },
)

function submit(): void {
    emit('submit', { name: form.name.trim() })
}
</script>

<template>
    <form class="spa-form" @submit.prevent="submit">
        <div class="form-field">
            <label for="club-name">{{ t('spa.clubs.name') }}</label>
            <InputText
                id="club-name"
                v-model="form.name"
                required
                :invalid="Boolean(errors.name)"
            />
            <small v-if="errors.name" class="field-error">{{
                errors.name
            }}</small>
        </div>
        <Button
            type="submit"
            :label="submitLabel"
            severity="success"
            :loading="pending"
        />
        <Message v-if="error" severity="error" :closable="false">{{
            error
        }}</Message>
    </form>
</template>
