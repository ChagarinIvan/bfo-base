<script setup lang="ts">
import { reactive, watch } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import { t } from '../../i18n'
import type { PersonPromptRequest } from '../../api/types'

const props = withDefaults(
    defineProps<{
        initialValue?: Partial<PersonPromptRequest>
        errors?: Record<string, string>
        pending?: boolean
        error?: string
    }>(),
    { initialValue: () => ({}), errors: () => ({}), pending: false, error: '' },
)
const emit = defineEmits<{ submit: [value: PersonPromptRequest] }>()
const form = reactive<PersonPromptRequest>({ prompt: '' })
watch(
    () => props.initialValue,
    (value) => {
        form.prompt = value?.prompt ?? ''
    },
    { immediate: true },
)
function submit(): void {
    emit('submit', { prompt: form.prompt })
}
</script>
<template>
    <form class="spa-form" @submit.prevent="submit">
        <div class="form-field">
            <label for="person-prompt">{{
                t('spa.person_prompt.prompt')
            }}</label
            ><InputText
                id="person-prompt"
                v-model="form.prompt"
                required
                maxlength="255"
                :invalid="Boolean(errors.prompt)"
            /><small v-if="errors.prompt" class="field-error">{{
                errors.prompt
            }}</small>
        </div>
        <Button
            type="submit"
            :label="t('spa.person_prompt.save')"
            severity="success"
            :loading="pending"
        />
        <Message v-if="error" severity="error" :closable="false">{{
            error
        }}</Message>
    </form>
</template>
