<script setup lang="ts">
import { onMounted, ref } from 'vue'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Message from 'primevue/message'
import MultiSelect from 'primevue/multiselect'
import { useToast } from 'primevue/usetoast'
import { useRoute, useRouter } from 'vue-router'
import { getCompetitionEvents, uniteEvents } from '../../api/events'
import type { Event } from '../../api/types'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const events = ref<Event[]>([])
const eventIds = ref<string[]>([])
const loading = ref(true)
const pending = ref(false)
const error = ref('')

async function load(): Promise<void> {
    try {
        const response = await getCompetitionEvents(
            String(route.params.competitionId),
            1,
            100,
        )
        events.value = response.data
    } catch {
        error.value = 'Не атрымалася загрузіць этапы.'
    } finally {
        loading.value = false
    }
}

async function submit(): Promise<void> {
    if (eventIds.value.length < 2) {
        error.value = 'Выберыце прынамсі два этапы.'
        return
    }
    pending.value = true
    error.value = ''
    try {
        const event = await uniteEvents(
            String(route.params.competitionId),
            eventIds.value,
        )
        toast.add({
            severity: 'success',
            summary: 'Этапы аб’яднаны.',
            life: 3000,
        })
        await router.push(`/app/events/${event.id}`)
    } catch {
        error.value = 'Не атрымалася аб’яднаць этапы.'
    } finally {
        pending.value = false
    }
}

onMounted(load)
</script>

<template>
    <Message v-if="loading" severity="info" :closable="false"
        >Загрузка этапаў…</Message
    >
    <Card v-else class="form-card">
        <template #title>Аб’яднаць этапы</template>
        <template #content>
            <form class="spa-form" @submit.prevent="submit">
                <div class="form-field">
                    <label for="unit-events">Этапы</label>
                    <MultiSelect
                        id="unit-events"
                        v-model="eventIds"
                        :options="events"
                        option-label="name"
                        option-value="id"
                        filter
                        display="chip"
                    >
                        <template #option="{ option }"
                            >{{ option.date }} — {{ option.name }}</template
                        >
                    </MultiSelect>
                </div>
                <Button
                    type="submit"
                    label="Аб’яднаць"
                    severity="success"
                    :loading="pending"
                />
                <Message v-if="error" severity="error" :closable="false">{{
                    error
                }}</Message>
            </form>
        </template>
    </Card>
</template>
