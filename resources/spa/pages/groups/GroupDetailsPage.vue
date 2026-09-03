<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue'
import Card from 'primevue/card'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import Paginator, { type PageState } from 'primevue/paginator'
import { useRoute } from 'vue-router'
import { getGroup } from '../../api/groups'
import { getGroupEvents } from '../../api/events'
import type { Event, Group, PaginationHeaders } from '../../api/types'
import FilterPanel from '../../components/FilterPanel.vue'
import { t } from '../../i18n'
import {
    debounce,
    paginationFromHeaders,
    resetPageOnFilterChange,
} from './groupModels'

const route = useRoute()
const group = ref<Group | null>(null)
const events = ref<Event[]>([])
const competitionName = ref('')
const year = ref('')
const date = ref('')
const loading = ref(true)
const error = ref('')
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    total: 0,
    lastPage: 1,
})
let latestRequest = 0
const debouncedFilter = debounce(
    () =>
        void loadEvents(resetPageOnFilterChange(pagination.value.currentPage)),
)

async function loadEvents(
    page = 1,
    perPage = pagination.value.perPage,
): Promise<void> {
    const id = String(route.params.id)
    const requestId = ++latestRequest
    const response = await getGroupEvents({
        groupId: id,
        withCompetition: 1,
        competitionName: competitionName.value || undefined,
        year: year.value || undefined,
        date: date.value || undefined,
        page,
        perPage,
    })
    if (requestId !== latestRequest) return
    events.value = response.data
    pagination.value = paginationFromHeaders(response.headers)
}
async function load(id: string): Promise<void> {
    loading.value = true
    error.value = ''
    try {
        group.value = await getGroup(id)
        await loadEvents()
    } catch {
        group.value = null
        error.value = t('spa.group.details.error')
    } finally {
        loading.value = false
    }
}
function onFilter(): void {
    if (competitionName.value && competitionName.value.trim().length < 3) return
    debouncedFilter()
}
function onPage(event: PageState): void {
    void loadEvents(event.page + 1, event.rows)
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
        <Card
            ><template #title>{{ group.name }}</template
            ><template #content
                ><table>
                    <tbody>
                        <tr>
                            <th>{{ t('spa.groups.distances_count') }}</th>
                            <td>{{ group.distancesCount }}</td>
                        </tr>
                    </tbody>
                </table></template
            ></Card
        >
        <h2 class="section-title">{{ t('spa.group.details.starts') }}</h2>
        <FilterPanel
            ><div class="filter-field">
                <label>{{ t('spa.group.filters.competition') }}</label
                ><InputText
                    v-model="competitionName"
                    @update:model-value="onFilter"
                />
            </div>
            <div class="filter-field">
                <label>{{ t('spa.group.filters.year') }}</label
                ><InputText v-model="year" @update:model-value="onFilter" />
            </div>
            <div class="filter-field">
                <label>{{ t('spa.group.filters.date') }}</label
                ><InputText
                    v-model="date"
                    type="date"
                    @update:model-value="onFilter"
                /></div
        ></FilterPanel>
        <Message v-if="!events.length" severity="secondary" :closable="false">{{
            t('spa.group.details.empty')
        }}</Message>
        <DataTable v-else :value="events" striped-rows
            ><Column
                field="competitionName"
                :header="t('spa.group.filters.competition')" /><Column
                field="name"
                :header="t('spa.group.details.start')" /><Column
                field="date"
                :header="t('spa.group.filters.date')" /><Column
                field="participantsCount"
                :header="t('spa.group.details.participants')"
        /></DataTable>
        <Paginator
            v-if="pagination.total"
            :first="(pagination.currentPage - 1) * pagination.perPage"
            :rows="pagination.perPage"
            :total-records="pagination.total"
            :rows-per-page-options="[10, 20, 50]"
            @page="onPage"
        />
    </template>
</template>
