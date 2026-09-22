<script setup lang="ts">
import axios from 'axios'
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import type { AxiosError } from 'axios'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import Select from 'primevue/select'
import type { PageState } from 'primevue/paginator'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { getClubOptions } from '../../api/clubs'
import { getCup, getCupEvent, getCupEventPoints } from '../../api/cups'
import { getEventsByIds } from '../../api/events'
import { getUsers } from '../../api/users'
import type {
    ClubOption,
    Cup,
    CupEvent,
    CupEventPoint,
    Event,
    PaginationHeaders,
    User,
} from '../../api/types'
import CupTypeIcon from '../../components/CupTypeIcon.vue'
import FilterPanel from '../../components/FilterPanel.vue'
import ImpressionDetails from '../../components/ImpressionDetails.vue'
import ListingTable from '../../components/ListingTable.vue'
import { t } from '../../i18n'
import { useAuthStore } from '../../stores/auth'
import {
    debounce,
    hasTooShortNameSearch,
    paginationFromHeaders,
} from '../listingModels'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const cupEvent = ref<CupEvent | null>(null)
const cup = ref<Cup | null>(null)
const event = ref<Event | null>(null)
const clubs = ref<Record<string, ClubOption>>({})
const users = ref<User[]>([])
const points = ref<CupEventPoint[]>([])
const groupId = ref<string | null>(null)
const name = ref('')
const loading = ref(true)
const pointsLoading = ref(false)
const pointsError = ref('')
const error = ref('')
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 50,
    hasNext: false,
})
let pointsRequestId = 0
let pointsController: AbortController | null = null
let loadRequestId = 0
let loadController: AbortController | null = null

const columns = computed(() => [
    { key: 'place', label: '№', defaultVisible: true },
    { key: 'person', label: t('app.common.fio'), defaultVisible: true },
    { key: 'year', label: t('app.common.birthday_year'), defaultVisible: true },
    { key: 'club', label: t('app.club.name'), defaultVisible: true },
    { key: 'time', label: t('app.common.time'), defaultVisible: true },
    { key: 'points', label: t('app.common.points'), defaultVisible: true },
])

function isNotFound(exception: unknown): boolean {
    return (
        typeof exception === 'object' &&
        exception !== null &&
        'isAxiosError' in exception &&
        exception.isAxiosError === true &&
        (exception as AxiosError).response?.status === 404
    )
}

function club(point: CupEventPoint): ClubOption | undefined {
    return point.personClubId ? clubs.value[point.personClubId] : undefined
}

async function loadPoints(
    page = 1,
    perPage = pagination.value.perPage,
): Promise<void> {
    if (!cupEvent.value || !groupId.value) return

    pointsController?.abort()
    const controller = new AbortController()
    pointsController = controller
    const requestId = ++pointsRequestId
    pointsLoading.value = true
    pointsError.value = ''
    try {
        const response = await getCupEventPoints(cupEvent.value.id, {
            groupId: groupId.value,
            ...(hasTooShortNameSearch(name.value) ? {} : { name: name.value }),
            page,
            perPage,
        }, controller.signal)
        if (requestId !== pointsRequestId) return
        points.value = response.data
        pagination.value = paginationFromHeaders(response.headers)
    } catch (exception) {
        if (axios.isCancel(exception) || requestId !== pointsRequestId) return
        points.value = []
        pointsError.value = t('spa.cup_event.error')
    } finally {
        if (requestId === pointsRequestId) pointsLoading.value = false
    }
}

async function load(cupEventId: string): Promise<void> {
    loadController?.abort()
    pointsController?.abort()
    const controller = new AbortController()
    loadController = controller
    const requestId = ++loadRequestId
    loading.value = true
    error.value = ''
    groupId.value = null
    try {
        cupEvent.value = await getCupEvent(cupEventId, controller.signal)
        if (requestId !== loadRequestId) return
        const [loadedCup, loadedEvents, clubOptions, loadedUsers] =
            await Promise.all([
                getCup(cupEvent.value.cupId, controller.signal),
                getEventsByIds([cupEvent.value.eventId], controller.signal),
                getClubOptions(controller.signal),
                auth.isAuthenticated
                    ? getUsers(controller.signal)
                    : Promise.resolve([]),
            ])
        if (requestId !== loadRequestId) return
        cup.value = loadedCup
        event.value = loadedEvents[0] ?? null
        clubs.value = Object.fromEntries(
            clubOptions.map((item) => [item.id, item]),
        )
        users.value = loadedUsers
        groupId.value = cup.value.groups[0]?.id ?? null
        await loadPoints()
    } catch (exception) {
        if (axios.isCancel(exception) || requestId !== loadRequestId) return
        cupEvent.value = null
        cup.value = null
        event.value = null
        points.value = []
        users.value = []
        if (isNotFound(exception)) {
            await router.replace({ name: 'not-found' })
            return
        }
        error.value = t('spa.cup_event.error')
    } finally {
        if (requestId === loadRequestId) loading.value = false
    }
}

const debouncedSearch = debounce(() => void loadPoints(1))
function onNameChange(): void {
    if (hasTooShortNameSearch(name.value)) {
        debouncedSearch.cancel()
        pointsController?.abort()
        pointsRequestId += 1
        pointsLoading.value = false
        points.value = []
        pointsError.value = ''
        pagination.value = { ...pagination.value, currentPage: 1 }
        return
    }
    debouncedSearch()
}
function onGroupChange(): void {
    void loadPoints(1)
}
function onPage(page: PageState): void {
    void loadPoints(page.page + 1, page.rows)
}

watch(
    () => String(route.params.cupEventId),
    (id) => void load(id),
    { immediate: true },
)
onBeforeUnmount(() => {
    debouncedSearch.cancel()
    pointsController?.abort()
    loadController?.abort()
})
</script>

<template>
    <Message v-if="loading" severity="info" :closable="false">{{
        t('spa.cup_event.loading')
    }}</Message>
    <Message v-else-if="error" severity="error" :closable="false">{{
        error
    }}</Message>
    <template v-else-if="cupEvent && cup && event">
        <Card class="competition-details-card">
            <template #title>{{ cup.name }}</template>
            <template #content>
                <table class="competition-details-info">
                    <tbody>
                        <tr>
                            <th>{{ t('spa.cup.type') }}</th>
                            <td>
                                <CupTypeIcon :type="cup.type" />
                                {{ t(`app.cup.type.${cup.type}` as never) }}
                            </td>
                        </tr>
                        <tr>
                            <th>{{ t('app.competition.name') }}</th>
                            <td>
                                <RouterLink
                                    :to="`/app/competitions/${event.competitionId}`"
                                >
                                    {{ event.competitionName }}
                                </RouterLink>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ t('spa.event.form.name') }}</th>
                            <td>
                                <RouterLink :to="`/app/events/${event.id}`">
                                    {{ event.name }}
                                </RouterLink>
                                — {{ event.date }}
                            </td>
                        </tr>
                        <tr>
                            <th>{{ t('app.common.points') }}</th>
                            <td>{{ cupEvent.points }}</td>
                        </tr>
                        <tr v-if="auth.isAuthenticated">
                            <th>{{ t('spa.cups.created') }}</th>
                            <td>
                                <ImpressionDetails
                                    :impression="cupEvent.created"
                                    :users="users"
                                    :label="t('spa.cups.created')"
                                />
                            </td>
                        </tr>
                        <tr v-if="auth.isAuthenticated">
                            <th>{{ t('spa.cups.updated') }}</th>
                            <td>
                                <ImpressionDetails
                                    :impression="cupEvent.updated"
                                    :users="users"
                                    :label="t('spa.cups.updated')"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </template>
        </Card>
        <h2 class="section-title">{{ t('spa.cup_event.standings') }}</h2>
        <ListingTable
            table-id="cup-event-points"
            :columns="columns"
            :items="points"
            :pagination="pagination"
            :loading="pointsLoading"
            :error="pointsError"
            :empty-label="t('spa.cup_event.empty')"
            :rows-per-page-options="[20, 50, 100]"
            @page="onPage"
        >
            <template #filters>
                <FilterPanel>
                    <div class="filter-field">
                        <label for="cup-event-group">{{
                            t('spa.cup_event.group')
                        }}</label>
                        <Select
                            id="cup-event-group"
                            v-model="groupId"
                            :options="cup.groups"
                            option-label="name"
                            option-value="id"
                            @update:model-value="onGroupChange"
                        />
                    </div>
                    <div class="filter-field">
                        <label for="cup-event-person-name">{{
                            t('spa.cup_event.person_filter')
                        }}</label>
                        <InputText
                            id="cup-event-person-name"
                            v-model="name"
                            @update:model-value="onNameChange"
                        />
                    </div>
                </FilterPanel>
            </template>
            <template #cell-place="{ index }">
                {{
                    (pagination.currentPage - 1) * pagination.perPage +
                    index +
                    1
                }}
            </template>
            <template #cell-person="{ data }">
                <RouterLink
                    v-if="data.personId"
                    :to="`/app/persons/${data.personId}`"
                >
                    {{ data.personName }}
                </RouterLink>
                <template v-else>{{ data.personName }}</template>
            </template>
            <template #cell-year="{ data }">{{ data.personYear }}</template>
            <template #cell-club="{ data }">
                <RouterLink
                    v-if="club(data)"
                    :to="`/app/clubs/${data.personClubId}`"
                >
                    {{ club(data)?.name }}
                </RouterLink>
            </template>
            <template #cell-time="{ data }">{{ data.time }}</template>
            <template #cell-points="{ data }">
                <strong
                    v-if="data.points === cupEvent.points"
                    class="text-info"
                    >{{ data.points }}</strong
                >
                <template v-else>{{ data.points }}</template>
            </template>
        </ListingTable>
    </template>
</template>
