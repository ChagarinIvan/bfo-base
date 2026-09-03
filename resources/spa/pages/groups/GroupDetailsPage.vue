<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import Card from 'primevue/card'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import Paginator, { type PageState } from 'primevue/paginator'
import Select from 'primevue/select'
import { useRoute, useRouter } from 'vue-router'
import { deleteGroup, getGroup } from '../../api/groups'
import { getUsers } from '../../api/users'
import { getYears } from '../../api/years'
import { getGroupEvents } from '../../api/events'
import type { Event, Group, PaginationHeaders, User } from '../../api/types'
import FilterPanel from '../../components/FilterPanel.vue'
import ImpressionDetails from '../../components/ImpressionDetails.vue'
import DateFilter from '../../components/DateFilter.vue'
import ConfirmDeleteDialog from '../../components/actions/ConfirmDeleteDialog.vue'
import GroupActionMenu from '../../components/actions/GroupActionMenu.vue'
import { t } from '../../i18n'
import { useAuthStore } from '../../stores/auth'
import {
    debounce,
    groupEventsQuery,
    hasTooShortNameSearch,
    paginationFromHeaders,
    resetPageOnFilterChange,
} from './groupModels'
import { yearSelectOptions } from '../competitions/competitionModels'

const route = useRoute()
const router = useRouter()
const group = ref<Group | null>(null)
const events = ref<Event[]>([])
const users = ref<User[]>([])
const years = ref<number[]>([])
const yearOptions = computed(() => yearSelectOptions(years.value))
const competitionName = ref('')
const year = ref<number | null>(null)
const date = ref('')
const loading = ref(true)
const error = ref('')
const deleteDialogVisible = ref(false)
const deletePending = ref(false)
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    total: 0,
    lastPage: 1,
})
const auth = useAuthStore()
let latestRequest = 0
const debouncedFilter = debounce(
    () =>
        void loadEvents(resetPageOnFilterChange(pagination.value.currentPage)),
)

async function onYearChange(value: number | null): Promise<void> {
    year.value = value
    await loadEvents(resetPageOnFilterChange(pagination.value.currentPage))
}

function onCompetitionNameChange(value: string | undefined): void {
    competitionName.value = value ?? ''

    if (hasTooShortNameSearch(competitionName.value)) {
        debouncedFilter.cancel()
        void loadEvents(resetPageOnFilterChange(pagination.value.currentPage))
        return
    }

    debouncedFilter()
}

async function onDateChange(value: string | undefined): Promise<void> {
    date.value = value ?? ''
    await loadEvents(resetPageOnFilterChange(pagination.value.currentPage))
}

async function loadEvents(
    page = 1,
    perPage = pagination.value.perPage,
): Promise<void> {
    const id = String(route.params.id)
    const requestId = ++latestRequest
    const response = await getGroupEvents(
        groupEventsQuery({
            groupId: id,
            withCompetition: 1,
            competitionName: competitionName.value,
            year: year.value ?? undefined,
            date: date.value,
            page,
            perPage,
        }),
    )
    if (requestId !== latestRequest) return
    events.value = response.data
    pagination.value = paginationFromHeaders(response.headers)
}
async function load(id: string): Promise<void> {
    loading.value = true
    error.value = ''
    try {
        group.value = await getGroup(id)
        users.value = auth.isAuthenticated ? await getUsers() : []
        years.value = await getYears()
        year.value =
            years.value.find((item) => item === new Date().getFullYear()) ??
            years.value[0] ??
            null
        await loadEvents()
    } catch {
        group.value = null
        error.value = t('spa.group.details.error')
    } finally {
        loading.value = false
    }
}
function onPage(event: PageState): void {
    void loadEvents(event.page + 1, event.rows)
}
async function remove(): Promise<void> {
    deletePending.value = true
    try {
        await deleteGroup(String(route.params.id))
        await router.push('/app/groups')
    } catch {
        error.value = t('spa.group.delete.error')
    } finally {
        deletePending.value = false
    }
}
watch(
    () => String(route.params.id),
    (id) => void load(id),
    { immediate: true },
)
onBeforeUnmount(() => debouncedFilter.cancel())
</script>

<template>
    <Message v-if="loading" severity="info" :closable="false">{{
        t('spa.groups.loading')
    }}</Message>
    <Message v-else-if="error" severity="error" :closable="false">{{
        error
    }}</Message>
    <template v-else-if="group">
        <Card class="group-details-card"
            ><template #title>{{ group.name }}</template
            ><template #content
                ><table class="group-details-info">
                    <tbody>
                        <tr>
                            <th scope="row">
                                {{ t('spa.groups.distances_count') }}
                            </th>
                            <td>{{ group.distancesCount }}</td>
                        </tr>
                        <tr v-if="auth.isAuthenticated">
                            <th scope="row">{{ t('spa.groups.created') }}</th>
                            <td>
                                <ImpressionDetails
                                    :impression="group.created"
                                    :users="users"
                                    :label="t('spa.groups.created')"
                                />
                            </td>
                        </tr>
                        <tr v-if="auth.isAuthenticated">
                            <th scope="row">{{ t('spa.groups.updated') }}</th>
                            <td>
                                <ImpressionDetails
                                    :impression="group.updated"
                                    :users="users"
                                    :label="t('spa.groups.updated')"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <GroupActionMenu
                    v-if="auth.isAuthenticated"
                    :group-id="group.id"
                    @merge="router.push(`/app/groups/${group.id}/merge`)"
                    @delete="deleteDialogVisible = true"
                /> </template
        ></Card>
        <h2 class="section-title">{{ t('spa.group.details.starts') }}</h2>
        <FilterPanel>
            <div class="filter-field">
                <label for="group-competition-name-filter">{{
                    t('spa.competitions.name_filter')
                }}</label>
                <InputText
                    id="group-competition-name-filter"
                    v-model="competitionName"
                    @update:model-value="onCompetitionNameChange"
                />
                <small
                    v-if="hasTooShortNameSearch(competitionName)"
                    class="filter-hint"
                    >{{ t('spa.competitions.name_hint') }}</small
                >
            </div>
            <div class="filter-field">
                <label for="group-event-year-filter">{{
                    t('spa.competitions.year')
                }}</label>
                <Select
                    id="group-event-year-filter"
                    v-model="year"
                    :options="yearOptions"
                    option-label="label"
                    option-value="value"
                    :placeholder="t('spa.competitions.year_placeholder')"
                    filter
                    filter-match-mode="contains"
                    :filter-placeholder="t('spa.competitions.year_filter')"
                    @update:model-value="onYearChange"
                />
            </div>
            <DateFilter
                v-model="date"
                input-id="group-event-date-filter"
                :label="t('spa.competitions.date_filter')"
                @update:model-value="onDateChange"
            />
        </FilterPanel>
        <Message v-if="!events.length" severity="secondary" :closable="false">{{
            t('spa.group.details.empty')
        }}</Message>
        <DataTable v-else :value="events" striped-rows
            ><Column :header="t('spa.group.filters.competition')">
                <template #body="{ data }">
                    <RouterLink :to="`/app/competitions/${data.competitionId}`">
                        {{ data.competitionName }}
                    </RouterLink>
                </template> </Column
            ><Column :header="t('spa.group.details.start')">
                <template #body="{ data }">
                    <a :href="`/events/${data.id}`">{{ data.name }}</a>
                </template> </Column
            ><Column
                field="date"
                :header="t('spa.group.filters.date')" /><Column
                field="participantsCount"
                :header="t('spa.group.details.participants')" /><Column
                v-if="auth.isAuthenticated"
                :header="t('spa.competitions.created')"
            >
                <template #body="{ data }">
                    <ImpressionDetails
                        :impression="data.created"
                        :users="users"
                        :label="t('spa.competitions.created')"
                    />
                </template> </Column
            ><Column
                v-if="auth.isAuthenticated"
                :header="t('spa.competitions.updated')"
            >
                <template #body="{ data }">
                    <ImpressionDetails
                        :impression="data.updated"
                        :users="users"
                        :label="t('spa.competitions.updated')"
                    />
                </template> </Column
        ></DataTable>
        <Paginator
            v-if="pagination.total"
            :first="(pagination.currentPage - 1) * pagination.perPage"
            :rows="pagination.perPage"
            :total-records="pagination.total"
            :rows-per-page-options="[10, 20, 50]"
            @page="onPage"
        />
        <ConfirmDeleteDialog
            :visible="deleteDialogVisible"
            :title="t('spa.group.delete')"
            :confirmation="t('spa.group.delete.confirm')"
            :cancel-label="t('spa.common.cancel')"
            :action-label="t('spa.group.delete')"
            :pending="deletePending"
            @cancel="deleteDialogVisible = false"
            @confirm="remove"
        />
    </template>
</template>
