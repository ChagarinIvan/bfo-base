<script setup lang="ts">
import { ref, watch } from 'vue'
import Card from 'primevue/card'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import Select from 'primevue/select'
import Message from 'primevue/message'
import { useRoute } from 'vue-router'
import InputText from 'primevue/inputtext'
import { useAuthStore } from '../../stores/auth'
import ActionButton from '../../components/actions/ActionButton.vue'
import { getEvent } from '../../api/events'
import { getEventDistances } from '../../api/distances'
import { getPersonProtocolLines } from '../../api/protocolLines'
import type { Distance, Event, ProtocolLine } from '../../api/types'

const route = useRoute()
const event = ref<Event | null>(null)
const distances = ref<Distance[]>([])
const distanceId = ref<string>()
const lines = ref<ProtocolLine[]>([])
const loading = ref(true)
const error = ref('')
const name = ref('')
const auth = useAuthStore()

async function loadLines(): Promise<void> {
    if (!distanceId.value) return
    lines.value = (await getPersonProtocolLines({ distanceId: distanceId.value, name: name.value || undefined, withClub: 1 })).data
}

async function load(): Promise<void> {
    loading.value = true
    error.value = ''
    try {
        const id = String(route.params.eventId)
        event.value = await getEvent(id)
        distances.value = await getEventDistances(id)
        distanceId.value = distances.value[0]?.id
        await loadLines()
    } catch {
        error.value = 'Не атрымалася загрузіць этап.'
    } finally {
        loading.value = false
    }
}

watch(() => String(route.params.eventId), () => void load(), { immediate: true })
watch(distanceId, () => void loadLines())
</script>

<template>
    <Message v-if="loading" severity="info">Загрузка этапу…</Message>
    <Message v-else-if="error" severity="error">{{ error }}</Message>
    <template v-else-if="event">
        <Card><template #title>{{ event.name }}</template><template #content>
            <p>{{ event.date }}</p><a :href="`/app/competitions/${event.competitionId}`">Спаборніцтва</a>
            <div v-if="auth.isAuthenticated"><ActionButton as="a" :href="`/events/${event.id}/edit`" label="Рэдагаваць" icon="pi pi-pencil" /></div>
        </template></Card>
        <Select v-model="distanceId" :options="distances" option-label="groupName" option-value="id" placeholder="Дыстанцыя" class="mt-3" />
        <InputText v-model="name" placeholder="Імя або прозвішча" class="mt-3" @change="loadLines" />
        <Message v-if="!distances.length" severity="secondary">Няма дыстанцый.</Message>
        <DataTable v-else :value="lines" striped-rows class="mt-3">
            <Column field="serialNumber" header="#" /><Column field="lastname" header="Прозвішча" /><Column field="firstname" header="Імя" /><Column field="club" header="Клуб" /><Column field="year" header="Год" /><Column field="rank" header="Разрад" /><Column field="time" header="Час" /><Column field="place" header="Месца" /><Column field="completeRank" header="Выкананне" />
            <Column v-if="auth.isAuthenticated" header="Дзеянні"><template #body="{ data }"><ActionButton as="a" :href="`/app/protocol-lines/${data.id}/person`" label="Прызначыць удзельніка" /></template></Column>
        </DataTable>
    </template>
</template>
