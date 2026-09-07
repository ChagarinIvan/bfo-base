<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import Card from 'primevue/card'
import Message from 'primevue/message'
import { useRoute, useRouter } from 'vue-router'
import { getClubOptions } from '../../api/clubs'
import { getPerson, updatePerson } from '../../api/persons'
import type { ClubOption, Person, PersonFormRequest } from '../../api/types'
import { t } from '../../i18n'
import { applyFieldErrors, isApiValidationError } from '../listingModels'
import PersonForm from './PersonForm.vue'
const route = useRoute()
const router = useRouter()
const person = ref<Person | null>(null)
const clubs = ref<ClubOption[]>([])
const errors = reactive<Record<string, string>>({})
const pending = ref(false)
const error = ref('')
onMounted(async () => {
    try {
        ;[person.value, clubs.value] = await Promise.all([
            getPerson(String(route.params.personId)),
            getClubOptions(),
        ])
    } catch {
        error.value = t('spa.person_view.not_found')
    }
})
async function submit(value: PersonFormRequest): Promise<void> {
    pending.value = true
    Object.keys(errors).forEach((key) => delete errors[key])
    try {
        const updated = await updatePerson(String(route.params.personId), value)
        await router.push(`/app/persons/${updated.id}`)
    } catch (exception: unknown) {
        if (isApiValidationError(exception)) {
            applyFieldErrors(exception.response.data.errors, errors)
        } else {
            error.value = t('spa.person.error')
        }
    } finally {
        pending.value = false
    }
}
</script>
<template>
    <Message v-if="error" severity="error" :closable="false">{{
        error
    }}</Message
    ><Card v-else-if="person" class="form-card"
        ><template #title>{{ t('spa.person.edit') }}</template
        ><template #content
            ><PersonForm
                :initial-value="person"
                :clubs="clubs"
                :errors="errors"
                :pending="pending"
                @submit="submit" /></template
    ></Card>
</template>
