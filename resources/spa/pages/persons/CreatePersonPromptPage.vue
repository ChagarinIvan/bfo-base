<script setup lang="ts">
import { reactive, ref } from 'vue'
import Card from 'primevue/card'
import { useRoute, useRouter } from 'vue-router'
import { createPersonPrompt } from '../../api/personPrompts'
import type { ApiErrorResponse, PersonPromptRequest } from '../../api/types'
import { isApiValidationError, applyFieldErrors } from '../listingModels'
import PersonPromptForm from './PersonPromptForm.vue'
import PersonPromptPersonInfo from '../../components/PersonPromptPersonInfo.vue'
import { t } from '../../i18n'
const route = useRoute()
const router = useRouter()
const pending = ref(false)
const error = ref('')
const fieldErrors = reactive<Record<string, string>>({})
async function submit(value: PersonPromptRequest): Promise<void> {
    pending.value = true
    error.value = ''
    Object.keys(fieldErrors).forEach((key) => delete fieldErrors[key])
    try {
        await createPersonPrompt(String(route.params.personId), value)
        await router.push(`/app/persons/${route.params.personId}/prompts`)
    } catch (exception: unknown) {
        if (isApiValidationError(exception))
            applyFieldErrors(
                (exception as { response: { data: ApiErrorResponse } }).response
                    .data.errors,
                fieldErrors,
            )
        else error.value = t('spa.person_prompt.save_error')
    } finally {
        pending.value = false
    }
}
</script>
<template>
    <PersonPromptPersonInfo :person-id="String(route.params.personId)" />
    <Card class="form-card"
        ><template #title>{{ t('spa.person_prompt.create_title') }}</template
        ><template #content
            ><PersonPromptForm
                :errors="fieldErrors"
                :pending="pending"
                :error="error"
                @submit="submit" /></template
    ></Card>
</template>
