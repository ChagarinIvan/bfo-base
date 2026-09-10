<script setup lang="ts">
import { ref } from 'vue'
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
const person = ref<Person | null>(null)
const searching = ref(false)
const pending = ref(false)
const error = ref('')
let latestSearch = 0

async function searchPeople(event: { value: string }): Promise<void> {
    const name = event.value.trim()
    const requestId = ++latestSearch

    if (name.length < 3) {
        people.value = []
        return
    }

    searching.value = true
    error.value = ''
    try {
        const result = await getPersons({ name, perPage: 20 })
        if (requestId !== latestSearch) return
        people.value = result.data
    } catch {
        if (requestId === latestSearch) {
            error.value = t('spa.protocol_line_person.error')
        }
    } finally {
        if (requestId === latestSearch) searching.value = false
    }
}

function personLabel(person: Person): string {
    const birthYear = person.birthday?.slice(0, 4) ?? '—'

    return `${person.lastname} ${person.firstname} (${birthYear})`
}

async function submit(): Promise<void> {
    if (!person.value) return
    pending.value = true
    error.value = ''
    try {
        await setProtocolLinePerson(
            String(route.params.protocolLineId),
            person.value.id,
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
            <form class="spa-form" @submit.prevent="submit">
                <div class="form-field">
                    <label for="protocol-line-person">{{
                        t('spa.protocol_line_person.person')
                    }}</label>
                    <Select
                        id="protocol-line-person"
                        v-model="person"
                        :options="people"
                        :option-label="personLabel"
                        filter
                        :loading="searching"
                        :placeholder="t('spa.protocol_line_person.placeholder')"
                        @filter="searchPeople"
                    />
                </div>
                <Button
                    type="submit"
                    :disabled="!person || pending"
                    :label="t('spa.protocol_line_person.save')"
                />
                <Message v-if="error" severity="error" :closable="false">
                    {{ error }}
                </Message>
            </form>
        </template>
    </Card>
</template>
