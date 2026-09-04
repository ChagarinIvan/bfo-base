<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import Card from 'primevue/card'
import Message from 'primevue/message'
import { useRoute, useRouter } from 'vue-router'
import { getPersonPrompt, updatePersonPrompt } from '../../api/personPrompts'
import type { PersonPrompt, PersonPromptRequest } from '../../api/types'
import { applyFieldErrors, isApiValidationError } from '../listingModels'
import PersonPromptForm from './PersonPromptForm.vue'
import { t } from '../../i18n'
const route = useRoute()
const router = useRouter()
const prompt = ref<PersonPrompt | null>(null)
const loading = ref(true)
const pending = ref(false)
const error = ref('')
const fieldErrors = reactive<Record<string, string>>({})
async function load(): Promise<void> {
    try {
        prompt.value = await getPersonPrompt(String(route.params.promptId))
    } catch {
        error.value = t('spa.person_prompt.error')
    } finally {
        loading.value = false
    }
}
async function submit(value: PersonPromptRequest): Promise<void> {
    pending.value = true
    Object.keys(fieldErrors).forEach((key) => delete fieldErrors[key])
    try {
        await updatePersonPrompt(String(route.params.promptId), value)
        await router.push(`/app/persons/${route.params.personId}/prompts`)
    } catch (exception: unknown) {
        if (isApiValidationError(exception))
            applyFieldErrors(exception.response.data.errors, fieldErrors)
        else error.value = t('spa.person_prompt.save_error')
    } finally {
        pending.value = false
    }
}
onMounted(() => void load())
</script>
<template>
    <Message
        v-if="loading || error"
        :severity="error ? 'error' : 'info'"
        :closable="false"
        >{{ error || t('spa.person_prompt.loading') }}</Message
    ><Card v-else-if="prompt"
        ><template #title>{{ t('spa.person_prompt.edit_title') }}</template
        ><template #content
            ><PersonPromptForm
                :initial-value="prompt"
                :errors="fieldErrors"
                :pending="pending"
                :error="error"
                @submit="submit" /></template
    ></Card>
</template>
