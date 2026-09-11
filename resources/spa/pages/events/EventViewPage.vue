<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import type { AxiosError } from 'axios'
import Card from 'primevue/card'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import Paginator, { type PageState } from 'primevue/paginator'
import Select from 'primevue/select'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import ActionButton from '../../components/actions/ActionButton.vue'
import ImpressionDetails from '../../components/ImpressionDetails.vue'
import { getEventDistances } from '../../api/distances'
import { getEvent } from '../../api/events'
import { getPersonProtocolLines } from '../../api/protocolLines'
import type {
    Distance,
    Event,
    PaginationHeaders,
    ProtocolLine,
    User,
} from '../../api/types'
import { getUsers } from '../../api/users'
import { paginationFromHeaders } from '../competitions/competitionModels'
import { useAuthStore } from '../../stores/auth'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const event = ref<Event | null>(null)
const distances = ref<Distance[]>([])
const distanceId = ref<string>()
const lines = ref<ProtocolLine[]>([])
const users = ref<User[]>([])
const loading = ref(true)
const linesLoading = ref(false)
const error = ref('')
const name = ref('')
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    total: 0,
    lastPage: 1,
})
const hasPoints = computed(() =>
    lines.value.some((line) => line.points !== null),
)
const hasVk = computed(() => lines.value.some((line) => line.vk))

function isNotFound(exception: unknown): boolean {
    return (
        typeof exception === 'object' &&
        exception !== null &&
        'isAxiosError' in exception &&
        exception.isAxiosError === true &&
        (exception as AxiosError).response?.status === 404
    )
}

async function loadLines(
    page = 1,
    perPage = pagination.value.perPage,
): Promise<void> {
    if (!distanceId.value) {
        lines.value = []
        return
    }

    linesLoading.value = true
    try {
        const response = await getPersonProtocolLines({
            distanceId: distanceId.value,
            name: name.value || undefined,
            withClub: 1,
            page,
            perPage,
        })
        lines.value = response.data
        pagination.value = paginationFromHeaders(response.headers)
    } finally {
        linesLoading.value = false
    }
}

async function load(eventId: string): Promise<void> {
    loading.value = true
    error.value = ''
    try {
        event.value = await getEvent(eventId)
        distances.value = await getEventDistances(eventId)
        users.value = auth.isAuthenticated ? await getUsers() : []
        distanceId.value = distances.value[0]?.id
        await loadLines()
    } catch (exception: unknown) {
        event.value = null
        distances.value = []
        lines.value = []
        if (isNotFound(exception)) {
            await router.replace({ name: 'not-found' })
            return
        }
        error.value = 'Не атрымалася загрузіць этап.'
    } finally {
        loading.value = false
    }
}

async function onDistanceChange(): Promise<void> {
    await loadLines(1)
}
async function onNameChange(): Promise<void> {
    await loadLines(1)
}
async function onPage(page: PageState): Promise<void> {
    await loadLines(page.page + 1, page.rows)
}

watch(
    () => String(route.params.eventId),
    (eventId) => void load(eventId),
    { immediate: true },
)
</script>

<template>
    <Message v-if="loading" severity="info" :closable="false"
        >Загрузка этапу…</Message
    >
    <Message v-else-if="error" severity="error" :closable="false">{{
        error
    }}</Message>
    <template v-else-if="event">
        <Card class="event-details-card">
            <template #title>{{ event.name }}</template>
            <template #content>
                <table class="event-details-info">
                    <tbody>
                        <tr>
                            <th scope="row">Дата</th>
                            <td>{{ event.date }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Апісанне</th>
                            <td>{{ event.description }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Спаборніцтва</th>
                            <td>
                                <RouterLink
                                    :to="`/app/competitions/${event.competitionId}`"
                                    >Спаборніцтва</RouterLink
                                >
                            </td>
                        </tr>
                        <tr v-if="auth.isAuthenticated">
                            <th scope="row">Створана</th>
                            <td>
                                <ImpressionDetails
                                    :impression="event.created"
                                    :users="users"
                                    label="Створана"
                                />
                            </td>
                        </tr>
                        <tr v-if="auth.isAuthenticated">
                            <th scope="row">Абноўлена</th>
                            <td>
                                <ImpressionDetails
                                    :impression="event.updated"
                                    :users="users"
                                    label="Абноўлена"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <ActionButton
                    v-if="auth.isAuthenticated"
                    as="a"
                    :href="`/events/${event.id}/edit`"
                    label="Рэдагаваць"
                    icon="pi pi-pencil"
                />
            </template>
        </Card>

        <Message
            v-if="!distances.length"
            severity="secondary"
            :closable="false"
            class="mt-3"
            >Няма дыстанцый.</Message
        >
        <template v-else>
            <div class="event-results-filters mt-3">
                <Select
                    v-model="distanceId"
                    :options="distances"
                    option-label="groupName"
                    option-value="id"
                    placeholder="Дыстанцыя"
                    @change="onDistanceChange"
                />
                <InputText
                    v-model="name"
                    placeholder="Імя або прозвішча"
                    @change="onNameChange"
                />
            </div>
            <Message
                v-if="linesLoading"
                severity="info"
                :closable="false"
                class="mt-3"
                >Загрузка пратаколу…</Message
            >
            <Message
                v-else-if="!lines.length"
                severity="secondary"
                :closable="false"
                class="mt-3"
                >Няма вынікаў.</Message
            >
            <template v-else>
                <DataTable :value="lines" striped-rows class="mt-3">
                    <Column field="serialNumber" header="#" />
                    <Column field="lastname" header="Прозвішча"
                        ><template #body="{ data }"
                            ><RouterLink
                                v-if="data.personId"
                                :to="`/app/persons/${data.personId}`"
                                >{{ data.lastname }}</RouterLink
                            ><span v-else>{{ data.lastname }}</span></template
                        ></Column
                    >
                    <Column field="firstname" header="Імя"
                        ><template #body="{ data }"
                            ><RouterLink
                                v-if="data.personId"
                                :to="`/app/persons/${data.personId}`"
                                >{{ data.firstname }}</RouterLink
                            ><span v-else>{{ data.firstname }}</span></template
                        ></Column
                    >
                    <Column field="club" header="Клуб"
                        ><template #body="{ data }"
                            ><RouterLink
                                v-if="data.clubId"
                                :to="`/app/clubs/${data.clubId}`"
                                >{{ data.club }}</RouterLink
                            ><span v-else>{{ data.club }}</span></template
                        ></Column
                    >
                    <Column field="year" header="Год" /><Column
                        field="rank"
                        header="Разрад"
                    /><Column field="time" header="Час" /><Column
                        field="place"
                        header="Месца"
                    /><Column field="completeRank" header="Выкананне" />
                    <Column
                        v-if="hasPoints"
                        field="points"
                        header="Балы"
                    /><Column v-if="hasVk" header="ВК"
                        ><template #body="{ data }">{{
                            data.vk ? 'ВК' : ''
                        }}</template></Column
                    >
                    <Column
                        v-if="auth.isAuthenticated"
                        field="activateRank"
                        header="Актывацыя"
                    />
                    <Column v-if="auth.isAuthenticated" header="Дзеянні"
                        ><template #body="{ data }"
                            ><ActionButton
                                as="a"
                                :href="`/app/protocol-lines/${data.id}/person`"
                                label="Прызначыць удзельніка" /></template
                    ></Column>
                </DataTable>
                <Paginator
                    :first="(pagination.currentPage - 1) * pagination.perPage"
                    :rows="pagination.perPage"
                    :total-records="pagination.total"
                    :rows-per-page-options="[20, 50, 100]"
                    class="mt-3"
                    @page="onPage"
                />
            </template>
        </template>
    </template>
</template>
