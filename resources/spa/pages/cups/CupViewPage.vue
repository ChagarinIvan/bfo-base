<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import type { AxiosError } from 'axios'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import type { PageState } from 'primevue/paginator'
import { useRoute, useRouter } from 'vue-router'
import { getCup, getCupEvents } from '../../api/cups'
import { getEventsByIds } from '../../api/events'
import type {
    Cup,
    CupEvent,
    Event,
    PaginationHeaders,
    User,
} from '../../api/types'
import ActionButton from '../../components/actions/ActionButton.vue'
import ConfirmDeleteDialog from '../../components/actions/ConfirmDeleteDialog.vue'
import DateFilter from '../../components/DateFilter.vue'
import FilterPanel from '../../components/FilterPanel.vue'
import ImpressionDetails from '../../components/ImpressionDetails.vue'
import ListingTable from '../../components/ListingTable.vue'
import { t } from '../../i18n'
import { useAuthStore } from '../../stores/auth'
import { getUsers } from '../../api/users'
import {
    debounce,
    hasTooShortNameSearch,
    paginationFromHeaders,
} from '../listingModels'
import { cupEventQuery } from './cupViewModels'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const cup = ref<Cup | null>(null)
const events = ref<CupEvent[]>([])
const stageEvents = ref<Record<string, Event>>({})
const users = ref<User[]>([])
const name = ref('')
const date = ref('')
const loading = ref(true)
const eventsLoading = ref(false)
const error = ref('')
const selectedEvent = ref<CupEvent | null>(null)
const deleteCupVisible = ref(false)
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 50,
    hasNext: false,
})

const columns = computed(() => [
    { key: 'name', label: t('spa.cups.name'), defaultVisible: true },
    {
        key: 'date',
        label: t('spa.competitions.date_filter'),
        defaultVisible: true,
        field: 'event.date',
    },
    { key: 'points', label: t('app.common.points'), defaultVisible: true },
    ...(auth.isAuthenticated
        ? [
              {
                  key: 'created',
                  label: t('spa.cups.created'),
                  defaultVisible: true,
              },
              {
                  key: 'updated',
                  label: t('spa.cups.updated'),
                  defaultVisible: true,
              },
              {
                  key: 'actions',
                  label: t('spa.cups.actions'),
                  defaultVisible: true,
              },
          ]
        : []),
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

async function loadEvents(
    page = 1,
    perPage = pagination.value.perPage,
): Promise<void> {
    if (!cup.value) return
    eventsLoading.value = true
    try {
        const response = await getCupEvents(
            cup.value.id,
            cupEventQuery({
                name: name.value,
                date: date.value,
                page,
                perPage,
            }),
        )
        events.value = response.data
        const loadedEvents = await getEventsByIds(
            response.data.map((item) => item.eventId),
        )
        stageEvents.value = Object.fromEntries(
            loadedEvents.map((event) => [event.id, event]),
        )
        pagination.value = paginationFromHeaders(response.headers)
    } finally {
        eventsLoading.value = false
    }
}

async function load(cupId: string): Promise<void> {
    loading.value = true
    error.value = ''
    try {
        cup.value = await getCup(cupId)
        users.value = auth.isAuthenticated ? await getUsers() : []
        await loadEvents()
    } catch (exception) {
        cup.value = null
        events.value = []
        if (isNotFound(exception)) {
            await router.replace({ name: 'not-found' })
            return
        }
        error.value = t('spa.cups.error')
    } finally {
        loading.value = false
    }
}

const debouncedSearch = debounce(() => void loadEvents(1))
function onNameChange(): void {
    if (hasTooShortNameSearch(name.value)) {
        debouncedSearch.cancel()
        void loadEvents(1)
        return
    }
    debouncedSearch()
}
function onDateChange(): void {
    void loadEvents(1)
}
function onPage(page: PageState): void {
    void loadEvents(page.page + 1, page.rows)
}
function deleteCup(): void {
    if (cup.value) window.location.assign(`/cups/${cup.value.id}/delete`)
}
function deleteEvent(): void {
    if (cup.value && selectedEvent.value)
        window.location.assign(
            `/cups/${cup.value.id}/${selectedEvent.value.id}/delete`,
        )
}
function cupTableUrl(): string {
    return cup.value?.groups[0]
        ? `/cups/${cup.value.id}/${cup.value.groups[0].id}/table`
        : ''
}

watch(
    () => String(route.params.cupId),
    (id) => void load(id),
    { immediate: true },
)
watch(
    () => auth.isAuthenticated,
    () => void load(String(route.params.cupId)),
)
onBeforeUnmount(() => debouncedSearch.cancel())
</script>

<template>
    <Message v-if="loading" severity="info" :closable="false">{{
        t('spa.cups.loading')
    }}</Message>
    <Message v-else-if="error" severity="error" :closable="false">{{
        error
    }}</Message>
    <template v-else-if="cup">
        <Card class="competition-details-card">
            <template #title>{{ cup.name }}</template>
            <template #content>
                <table class="competition-details-info">
                    <tbody>
                        <tr>
                            <th>{{ t('spa.cup.form.year') }}</th>
                            <td>{{ cup.year }}</td>
                        </tr>
                        <tr>
                            <th>{{ t('spa.cup.type') }}</th>
                            <td>
                                {{ t(`app.cup.type.${cup.type}` as never) }}
                            </td>
                        </tr>
                        <tr>
                            <th>{{ t('spa.cups.events_count') }}</th>
                            <td>{{ cup.eventsCount }}</td>
                        </tr>
                        <tr>
                            <th>{{ t('spa.cups.groups') }}</th>
                            <td>
                                {{
                                    cup.groups
                                        .map((group) => group.name)
                                        .join(', ')
                                }}
                            </td>
                        </tr>
                        <tr>
                            <th>{{ t('spa.cups.visibility') }}</th>
                            <td>
                                <span class="mass-competition-indicator__icon">
                                    <i
                                        :class="[
                                            cup.visible
                                                ? 'pi pi-check-square'
                                                : 'pi pi-times-circle',
                                            'mass-icon',
                                            cup.visible
                                                ? 'mass-icon--active'
                                                : 'mass-icon--inactive',
                                        ]"
                                        :aria-label="
                                            cup.visible
                                                ? t('spa.cups.visible_yes')
                                                : t('spa.cups.visible_no')
                                        "
                                        :title="
                                            cup.visible
                                                ? t('spa.cups.visible_yes')
                                                : t('spa.cups.visible_no')
                                        "
                                        role="img"
                                    />
                                </span>
                            </td>
                        </tr>
                        <tr v-if="auth.isAuthenticated">
                            <th>{{ t('spa.cups.created') }}</th>
                            <td>
                                <ImpressionDetails
                                    :impression="cup.created"
                                    :users="users"
                                    :label="t('spa.cups.created')"
                                />
                            </td>
                        </tr>
                        <tr v-if="auth.isAuthenticated">
                            <th>{{ t('spa.cups.updated') }}</th>
                            <td>
                                <ImpressionDetails
                                    :impression="cup.updated"
                                    :users="users"
                                    :label="t('spa.cups.updated')"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="auth.isAuthenticated" class="action-menu">
                    <ActionButton
                        as="a"
                        :href="`/cups/${cup.id}/event/create`"
                        icon="pi pi-plus"
                        :label="t('app.competition.add_event')"
                        severity="success"
                    />
                    <ActionButton
                        as="a"
                        :href="`/cups/${cup.id}/cache`"
                        icon="pi pi-refresh"
                        :label="t('app.common.cache_clear')"
                        severity="warn"
                    />
                    <ActionButton
                        as="a"
                        :href="`/cups/${cup.id}/export`"
                        icon="pi pi-download"
                        :label="t('app.cup.table.export')"
                        severity="info"
                    />
                    <ActionButton
                        v-if="cupTableUrl()"
                        as="a"
                        :href="cupTableUrl()"
                        icon="pi pi-table"
                        :label="t('spa.cups.table')"
                    />
                    <ActionButton
                        icon="pi pi-trash"
                        :label="t('spa.cups.delete.action')"
                        severity="danger"
                        @click="deleteCupVisible = true"
                    />
                </div>
            </template>
        </Card>
        <h2 class="section-title">{{ t('app.cup.events') }}</h2>
        <ListingTable
            table-id="cup-events"
            :columns="columns"
            :authenticated="auth.isAuthenticated"
            :items="events"
            :pagination="pagination"
            :loading="eventsLoading"
            :empty-label="t('spa.cups.empty')"
            :rows-per-page-options="[20, 50, 100]"
            @page="onPage"
        >
            <template #filters
                ><FilterPanel
                    ><div class="filter-field">
                        <label for="cup-event-name-filter">{{
                            t('spa.cups.name_filter')
                        }}</label
                        ><InputText
                            id="cup-event-name-filter"
                            v-model="name"
                            @update:model-value="onNameChange"
                        />
                    </div>
                    <DateFilter
                        v-model="date"
                        input-id="cup-event-date-filter"
                        :label="t('spa.competitions.date_filter')"
                        @update:model-value="onDateChange" /></FilterPanel
            ></template>
            <template #cell-name="{ data }">
                <RouterLink
                    v-if="stageEvents[data.eventId]"
                    :to="`/app/events/${data.eventId}`"
                >
                    {{ stageEvents[data.eventId].competitionName }} -
                    {{ stageEvents[data.eventId].name }}
                </RouterLink>
            </template>
            <template #cell-date="{ data }">
                {{ stageEvents[data.eventId]?.date }}
            </template>
            <template #cell-created="{ data }"
                ><ImpressionDetails
                    :impression="data.created"
                    :users="users"
                    :label="t('spa.cups.created')"
            /></template>
            <template #cell-updated="{ data }"
                ><ImpressionDetails
                    :impression="data.updated"
                    :users="users"
                    :label="t('spa.cups.updated')"
            /></template>
            <template #cell-actions="{ data }"
                ><ActionButton
                    as="a"
                    :href="`/cups/${cup.id}/${data.id}/edit`"
                    icon="pi pi-pencil"
                    :label="t('spa.cups.edit.action')" /><ActionButton
                    icon="pi pi-trash"
                    :label="t('spa.cups.delete.action')"
                    severity="danger"
                    @click="selectedEvent = data"
            /></template>
        </ListingTable>
        <ConfirmDeleteDialog
            v-if="auth.isAuthenticated"
            :visible="deleteCupVisible"
            :title="t('spa.cups.delete.title')"
            :confirmation="t('spa.cups.delete.confirm', { name: cup.name })"
            :cancel-label="t('spa.cups.delete.cancel')"
            :action-label="t('spa.cups.delete.action')"
            @cancel="deleteCupVisible = false"
            @confirm="deleteCup"
        />
        <ConfirmDeleteDialog
            v-if="auth.isAuthenticated && selectedEvent"
            :visible="Boolean(selectedEvent)"
            :title="t('spa.event.delete.title')"
            :confirmation="
                t('spa.event.delete.confirm', {
                    name: stageEvents[selectedEvent.eventId]?.name ?? '',
                })
            "
            :cancel-label="t('spa.event.delete.cancel')"
            :action-label="t('spa.event.delete.action')"
            @cancel="selectedEvent = null"
            @confirm="deleteEvent"
        />
    </template>
</template>
