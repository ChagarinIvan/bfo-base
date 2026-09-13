<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue'
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
import EventProcessingStatus from '../../components/EventProcessingStatus.vue'
import ListingTable from '../../components/ListingTable.vue'
import { getEventDistances } from '../../api/distances'
import { getCompetition } from '../../api/competitions'
import { deleteEvent, getEvent } from '../../api/events'
import ConfirmDeleteDialog from '../../components/actions/ConfirmDeleteDialog.vue'
import { getPersonProtocolLines } from '../../api/protocolLines'
import type {
    Distance,
    Competition,
    Event,
    EventProcessingStatus as EventProcessingState,
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
const processingRefreshError = ref(false)
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
const deleting = ref(false)
const deleteDialogVisible = ref(false)
const columns = computed(() => [
    { key: 'number', label: '#', defaultVisible: true },
    { key: 'lastname', label: 'Прозвішча', defaultVisible: true },
    { key: 'firstname', label: 'Імя', defaultVisible: true },
    { key: 'club', label: 'Клуб', defaultVisible: true },
    { key: 'year', label: 'Год', defaultVisible: true },
    { key: 'rank', label: 'Разрад', defaultVisible: true },
    { key: 'time', label: 'Час', defaultVisible: true },
    { key: 'place', label: 'Месца', defaultVisible: true },
    { key: 'completeRank', label: 'Выкананне', defaultVisible: true },
    ...(hasPoints.value
        ? [{ key: 'points', label: 'Балы', defaultVisible: true }]
        : []),
    ...(hasVk.value ? [{ key: 'vk', label: 'ВК', defaultVisible: true }] : []),
    ...(auth.isAuthenticated
        ? [
              {
                  key: 'activateRank',
                  label: 'Актывацыя разраду',
                  defaultVisible: true,
              },
              { key: 'actions', label: 'Дзеянні', defaultVisible: true },
          ]
        : []),
])
let targetScrolled = false
let targetScrollTimer: number | undefined
let processingTimer: number | undefined

function needsProcessingPolling(
    status: EventProcessingState | null | undefined,
): boolean {
    return ['queued', 'parsing', 'identifying', 'rebuildingRanks'].includes(
        status ?? '',
    )
}

function stopProcessingPolling(): void {
    if (processingTimer !== undefined) {
        window.clearInterval(processingTimer)
        processingTimer = undefined
    }
}

async function refreshProcessing(): Promise<void> {
    if (!event.value || !needsProcessingPolling(event.value.processingStatus)) {
        stopProcessingPolling()
        return
    }

    try {
        event.value = await getEvent(event.value.id)
        processingRefreshError.value = false
        await loadLines()
        if (!needsProcessingPolling(event.value.processingStatus)) {
            stopProcessingPolling()
        }
    } catch {
        processingRefreshError.value = true
    }
}

function startProcessingPolling(): void {
    stopProcessingPolling()
    if (needsProcessingPolling(event.value?.processingStatus)) {
        processingTimer = window.setInterval(
            () => void refreshProcessing(),
            5000,
        )
    }
}
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

function targetProtocolLineHash(): string {
    return window.location.hash || route.hash
}

function targetProtocolLineId(): string | undefined {
    const hash = targetProtocolLineHash().slice(1)
    if (hash === '') return undefined

    return hash.startsWith('protocol-line-')
        ? hash.slice('protocol-line-'.length)
        : hash
}

function highlightProtocolLineTarget(target: HTMLElement): void {
    const cell = target.closest('td')
    if (cell === null) return

    cell.classList.remove('protocol-line-anchor-highlight')
    void cell.offsetWidth
    cell.classList.add('protocol-line-anchor-highlight')
    window.setTimeout(() => {
        cell.classList.remove('protocol-line-anchor-highlight')
    }, 1800)
}

function scheduleTargetProtocolLineScroll(): void {
    const targetId = targetProtocolLineId()
    if (targetScrolled || targetId === undefined) return

    if (targetScrollTimer !== undefined) {
        window.clearInterval(targetScrollTimer)
    }

    let attempts = 0
    targetScrollTimer = window.setInterval(() => {
        const target =
            document.getElementById(targetId) ??
            document.getElementById(protocolLineAnchor(targetId))
        if (target && !targetScrolled) {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' })
            highlightProtocolLineTarget(target)
            targetScrolled = true
            window.clearInterval(targetScrollTimer)
            targetScrollTimer = undefined
            return
        }

        attempts++
        if (attempts >= 100) {
            window.clearInterval(targetScrollTimer)
            targetScrollTimer = undefined
        }
    }, 50)
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
        processingRefreshError.value = false
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
        startProcessingPolling()
    } catch (exception: unknown) {
        event.value = null
        competition.value = null
        distances.value = []
        lines.value = []
        stopProcessingPolling()
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

async function deleteCurrentEvent(): Promise<void> {
    if (!event.value) return
    deleting.value = true
    try {
        const competitionId = event.value.competitionId
        await deleteEvent(event.value.id)
        await router.push(`/app/competitions/${competitionId}`)
    } catch {
        error.value = t('spa.event.delete.error')
    } finally {
        deleting.value = false
        deleteDialogVisible.value = false
    }
}

watch(
    () => String(route.params.eventId),
    (eventId) => void load(eventId),
    { immediate: true },
)

onBeforeUnmount(() => {
    debouncedNameSearch.cancel()
    if (targetScrollTimer !== undefined) {
        window.clearInterval(targetScrollTimer)
    }
    stopProcessingPolling()
})
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
                <table
                    class="competition-details-info"
                    :class="{
                        'details-info--with-actions': auth.isAuthenticated,
                    }"
                >
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
                                    >{{
                                        competition?.name ?? 'Спаборніцтва'
                                    }}</RouterLink
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
                        :href="`/app/events/${event.id}/edit`"
                        label="Рэдагаваць"
                        icon="pi pi-pencil"
                        severity="secondary"
                    />
                    <ActionButton
                        icon="pi pi-trash"
                        :label="t('spa.event.delete.action')"
                        severity="danger"
                        @click="deleteDialogVisible = true"
                    />
                </div>
            </template>
        </Card>

        <EventProcessingStatus
            v-if="auth.isAuthenticated && event.processingStatus"
            :status="event.processingStatus"
            :refresh-error="processingRefreshError"
        />

        <h2 class="section-title">Вынікі</h2>
        <Message
            v-if="!distances.length"
            severity="secondary"
            :closable="false"
            class="mt-3"
            >Няма дыстанцый.</Message
        >
        <template v-else>
            <ListingTable
                table-id="event-protocol-lines"
                :columns="columns"
                :authenticated="auth.isAuthenticated"
            >
                <template #filters>
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
                            <label for="event-name-filter"
                                >Імя або прозвішча</label
                            >
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
                </template>
                <template #default="{ isVisible }">
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
                        <DataTable
                            :value="lines"
                            striped-rows
                            class="events-table"
                        >
                            <Column
                                v-if="isVisible('number')"
                                field="serialNumber"
                                header="#"
                                ><template #body="{ data }"
                                    ><span :id="protocolLineAnchor(data.id)">{{
                                        data.serialNumber
                                    }}</span></template
                                ></Column
                            >
                            <Column
                                v-if="isVisible('lastname')"
                                field="lastname"
                                header="Прозвішча"
                                ><template #body="{ data }"
                                    ><RouterLink
                                        v-if="data.personId"
                                        :to="`/app/persons/${data.personId}`"
                                        >{{ data.lastname }}</RouterLink
                                    ><span v-else>{{
                                        data.lastname
                                    }}</span></template
                                ></Column
                            >
                            <Column
                                v-if="isVisible('firstname')"
                                field="firstname"
                                header="Імя"
                                ><template #body="{ data }"
                                    ><RouterLink
                                        v-if="data.personId"
                                        :to="`/app/persons/${data.personId}`"
                                        >{{ data.firstname }}</RouterLink
                                    ><span v-else>{{
                                        data.firstname
                                    }}</span></template
                                ></Column
                            >
                            <Column
                                v-if="isVisible('club')"
                                field="club"
                                header="Клуб"
                                ><template #body="{ data }"
                                    ><RouterLink
                                        v-if="data.clubId"
                                        :to="`/app/clubs/${data.clubId}`"
                                        >{{ data.club }}</RouterLink
                                    ><span v-else>{{
                                        data.club
                                    }}</span></template
                                ></Column
                            >
                            <Column
                                v-if="isVisible('year')"
                                field="year"
                                header="Год"
                            /><Column
                                v-if="isVisible('rank')"
                                field="rank"
                                header="Разрад"
                            /><Column
                                v-if="isVisible('time')"
                                field="time"
                                header="Час"
                            /><Column
                                v-if="isVisible('place')"
                                field="place"
                                header="Месца"
                            /><Column
                                v-if="isVisible('completeRank')"
                                field="completeRank"
                                header="Выкананне"
                            />
                            <Column
                                v-if="hasPoints && isVisible('points')"
                                field="points"
                                header="Балы"
                            /><Column
                                v-if="hasVk && isVisible('vk')"
                                header="ВК"
                                ><template #body="{ data }">{{
                                    data.vk ? 'ВК' : ''
                                }}</template></Column
                            >
                            <Column
                                v-if="
                                    auth.isAuthenticated &&
                                    isVisible('activateRank')
                                "
                                field="activateRank"
                                header="Актывацыя разраду"
                            />
                            <Column
                                v-if="
                                    auth.isAuthenticated && isVisible('actions')
                                "
                                header="Дзеянні"
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
                            :first="
                                (pagination.currentPage - 1) *
                                pagination.perPage
                            "
                            :rows="pagination.perPage"
                            :total-records="pagination.total"
                            :rows-per-page-options="[20, 50, 100]"
                            class="competitions-paginator"
                            @page="onPage"
                        />
                    </template>
                </template>
            </ListingTable>
        </template>
        <ConfirmDeleteDialog
            v-if="auth.isAuthenticated"
            :visible="deleteDialogVisible"
            :title="t('spa.event.delete.title')"
            :confirmation="t('spa.event.delete.confirm', { name: event.name })"
            :cancel-label="t('spa.event.delete.cancel')"
            :action-label="t('spa.event.delete.action')"
            :pending="deleting"
            @cancel="deleteDialogVisible = false"
            @confirm="deleteCurrentEvent"
        />
    </template>
</template>
