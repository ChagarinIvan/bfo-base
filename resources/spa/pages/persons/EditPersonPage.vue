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
const loading = ref(true)
const loadError = ref('')
const submitError = ref('')

async function load(): Promise<void> {
    try {
        ;[person.value, clubs.value] = await Promise.all([
            getPerson(String(route.params.personId)),
            getClubOptions(),
        ])
    } catch (exception: unknown) {
        const status = (exception as { response?: { status?: number } })
            .response?.status
        loadError.value =
            status === 404
                ? t('spa.person_view.not_found')
                : t('spa.person.error')
    } finally {
        loading.value = false
    }
}

onMounted(() => void load())

async function submit(value: PersonFormRequest): Promise<void> {
    pending.value = true
    submitError.value = ''
    Object.keys(errors).forEach((key) => delete errors[key])
    try {
        const updated = await updatePerson(String(route.params.personId), value)
        await router.push({
            path: `/app/persons/${updated.id}`,
            query: { refresh: String(Date.now()) },
        })
    } catch (exception: unknown) {
        if (isApiValidationError(exception)) {
            applyFieldErrors(exception.response.data.errors, errors)
        } else {
            submitError.value = t('spa.person.error')
        }
    } finally {
        pending.value = false
    }
}
</script>
<template>
    <Message
        v-if="loading || loadError"
        :severity="loadError ? 'error' : 'info'"
        :closable="false"
        >{{ loadError || t('spa.person.loading') }}</Message
    ><Card v-else-if="person" class="form-card"
        ><template #title>{{ t('spa.person.edit') }}</template
        ><template #content
            ><PersonForm
                :initial-value="person"
                :clubs="clubs"
                :errors="errors"
                :pending="pending"
                @submit="submit"
            /><Message v-if="submitError" severity="error" :closable="false">{{
                submitError
            }}</Message></template
        ></Card
    >
</template>
