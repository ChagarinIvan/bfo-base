<script setup lang="ts">
import {
    computed,
    onBeforeUnmount,
    ref,
    type ComponentPublicInstance,
    watch,
} from 'vue'
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
import FilterPanel from '../../components/FilterPanel.vue'
import ImpressionDetails from '../../components/ImpressionDetails.vue'
import { getEventDistances } from '../../api/distances'
import { getCompetition } from '../../api/competitions'
import { getEvent } from '../../api/events'
import { getPersonProtocolLines } from '../../api/protocolLines'
import type {
    Distance,
    Competition,
    Event,
    PaginationHeaders,
    ProtocolLine,
    User,
} from '../../api/types'
import { getUsers } from '../../api/users'
import { t } from '../../i18n'
import { paginationFromHeaders } from '../competitions/competitionModels'
import {
    debounce,
    hasTooShortNameSearch,
    normaliseNameSearch,
} from '../listingModels'
import { useAuthStore } from '../../stores/auth'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const event = ref<Event | null>(null)
const competition = ref<Competition | null>(null)
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
    perPage: 100,
    total: 0,
    lastPage: 1,
})
const hasPoints = computed(() =>
    lines.value.some((line) => line.points !== null),
)
const hasVk = computed(() => lines.value.some((line) => line.vk))
let targetScrolled = false
const debouncedNameSearch = debounce(() => {
    void loadLines(1)
})

function isNotFound(exception: unknown): boolean {
    return (
        typeof exception === 'object' &&
        exception !== null &&
        'isAxiosError' in exception &&
        exception.isAxiosError === true &&
        (exception as AxiosError).response?.status === 404
    )
}

function requestedDistanceId(): string | undefined {
    const value = route.query.distanceId

    return typeof value === 'string' ? value : undefined
}

function protocolLineAnchor(id: string): string {
    return 'protocol-line-' + id
}

function scrollToTargetProtocolLine(
    element: Element | ComponentPublicInstance | null,
    id: string,
): void {
    if (targetScrolled || !route.hash.startsWith('#protocol-line-')) return

    const target = element instanceof Element ? element : element?.$el
    if (route.hash.slice(1) !== protocolLineAnchor(id) || !(target instanceof Element)) {
        return
    }

    target.scrollIntoView({ behavior: 'smooth', block: 'center' })
    targetScrolled = true
}

function scheduleTargetProtocolLineScroll(): void {
    if (targetScrolled || !route.hash.startsWith('#protocol-line-')) return

    let attempts = 0
    const scrollWhenRendered = (): void => {
        const target = document.getElementById(route.hash.slice(1))
        if (target && !targetScrolled) {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' })
            targetScrolled = true
            return
        }

        attempts++
        if (attempts < 20 && !targetScrolled) {
            window.setTimeout(scrollWhenRendered, 50)
        }
    }

    window.setTimeout(scrollWhenRendered)
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
            name: hasTooShortNameSearch(name.value)
                ? undefined
                : normaliseNameSearch(name.value) || undefined,
            withClub: 1,
            page,
            perPage,
        })
        lines.value = response.data
        pagination.value = paginationFromHeaders(response.headers)
        scheduleTargetProtocolLineScroll()
    } finally {
        linesLoading.value = false
    }
}

async function load(eventId: string): Promise<void> {
    loading.value = true
    error.value = ''
    try {
        event.value = await getEvent(eventId)
        const [loadedCompetition, loadedDistances] = await Promise.all([
            getCompetition(event.value.competitionId),
            getEventDistances(eventId),
        ])
        competition.value = loadedCompetition
        distances.value = loadedDistances
        users.value = auth.isAuthenticated ? await getUsers() : []
        const requestedId = requestedDistanceId()
        distanceId.value = distances.value.some(
            (distance) => distance.id === requestedId,
        )
            ? requestedId
            : distances.value[0]?.id
        await loadLines()
    } catch (exception: unknown) {
        event.value = null
        competition.value = null
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
function onNameChange(value: string | undefined): void {
    name.value = value ?? ''

    if (hasTooShortNameSearch(name.value)) {
        debouncedNameSearch.cancel()
        void loadLines(1)
        return
    }

    debouncedNameSearch()
}
async function onPage(page: PageState): Promise<void> {
    await loadLines(page.page + 1, page.rows)
}

watch(
    () => String(route.params.eventId),
    (eventId) => void load(eventId),
    { immediate: true },
)

onBeforeUnmount(() => debouncedNameSearch.cancel())
</script>

<template>
    <Message v-if="loading" severity="info" :closable="false"
        >Загрузка этапу…</Message
    >
    <Message v-else-if="error" severity="error" :closable="false">{{
        error
    }}</Message>
    <template v-else-if="event">
        <Card class="competition-details-card">
            <template #title>{{ event.name }}</template>
            <template #content>
                <table class="competition-details-info">
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
                                    >{{ competition?.name ?? 'Спаборніцтва' }}</RouterLink
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
                <div v-if="auth.isAuthenticated" class="details-actions">
                    <ActionButton
                        as="a"
                        :href="`/events/${event.id}/edit`"
                        label="Рэдагаваць"
                        icon="pi pi-pencil"
                        severity="secondary"
                    />
                </div>
            </template>
        </Card>

        <h2 class="section-title">Вынікі</h2>
        <Message
            v-if="!distances.length"
            severity="secondary"
            :closable="false"
            class="mt-3"
            >Няма дыстанцый.</Message
        >
        <template v-else>
            <FilterPanel>
                <div class="filter-field">
                    <label for="event-distance-filter">Дыстанцыя</label>
                    <Select
                        id="event-distance-filter"
                        v-model="distanceId"
                        :options="distances"
                        option-label="groupName"
                        option-value="id"
                        filter
                        filter-match-mode="contains"
                        @change="onDistanceChange"
                    />
                </div>
                <div class="filter-field">
                    <label for="event-name-filter">Імя або прозвішча</label>
                    <InputText
                        id="event-name-filter"
                        v-model="name"
                        @update:model-value="onNameChange"
                    />
                    <small
                        v-if="hasTooShortNameSearch(name)"
                        class="filter-hint"
                        >{{ t('spa.competitions.name_hint') }}</small
                    >
                </div>
            </FilterPanel>
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
                <DataTable :value="lines" striped-rows class="events-table">
                    <Column field="serialNumber" header="#"
                        ><template #body="{ data }"
                            ><span
                                :id="protocolLineAnchor(data.id)"
                                :ref="(element) => scrollToTargetProtocolLine(element, data.id)"
                                >{{ data.serialNumber }}</span
                            ></template
                        ></Column
                    >
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
                        header="Актывацыя разраду"
                    />
                    <Column v-if="auth.isAuthenticated" header="Дзеянні"
                        ><template #body="{ data }"
                            ><ActionButton
                                as="a"
                                :href="`/app/protocol-lines/${data.id}/person`"
                                label="Прызначыць удзельніка"
                                icon="pi pi-user-plus"
                                severity="success" /></template
                    ></Column>
                </DataTable>
                <Paginator
                    :first="(pagination.currentPage - 1) * pagination.perPage"
                    :rows="pagination.perPage"
                    :total-records="pagination.total"
                    :rows-per-page-options="[20, 50, 100]"
                    class="competitions-paginator"
                    @page="onPage"
                />
            </template>
        </template>
    </template>
</template>
