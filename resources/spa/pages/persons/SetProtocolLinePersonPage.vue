<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Message from 'primevue/message'
import Select from 'primevue/select'
import { getPersons } from '../../api/persons'
import { setProtocolLinePerson } from '../../api/protocolLines'
import type { Person } from '../../api/types'
import { t } from '../../i18n'

const route = useRoute()
const router = useRouter()
const people = ref<Person[]>([])
const personId = ref<string | null>(null)
const loading = ref(true)
const pending = ref(false)
const error = ref('')

onMounted(async () => {
    try {
        people.value = (await getPersons({ perPage: 1000 })).data
    } catch {
        error.value = t('spa.protocol_line_person.error')
    } finally {
        loading.value = false
    }
})

async function submit(): Promise<void> {
    if (!personId.value) return
    pending.value = true
    error.value = ''
    try {
        await setProtocolLinePerson(
            String(route.params.protocolLineId),
            personId.value,
        )
        await router.back()
    } catch {
        error.value = t('spa.protocol_line_person.save_error')
    } finally {
        pending.value = false
    }
}
</script>

<template>
    <Card class="form-card">
        <template #title>{{ t('spa.protocol_line_person.title') }}</template>
        <template #content>
            <Message v-if="loading" severity="info" :closable="false">
                {{ t('spa.protocol_line_person.loading') }}
            </Message>
            <form v-else class="spa-form" @submit.prevent="submit">
                <div class="form-field">
                    <label for="protocol-line-person">{{
                        t('spa.protocol_line_person.person')
                    }}</label>
                    <Select
                        id="protocol-line-person"
                        v-model="personId"
                        :options="people"
                        option-value="id"
                        :option-label="
                            (person: Person) =>
                                `${person.lastname} ${person.firstname}`
                        "
                        filter
                        :placeholder="t('spa.protocol_line_person.placeholder')"
                    />
                </div>
                <Button
                    type="submit"
                    :disabled="!personId || pending"
                    :label="t('spa.protocol_line_person.save')"
                />
                <Message v-if="error" severity="error" :closable="false">
                    {{ error }}
                </Message>
            </form>
        </template>
    </Card>
</template>
