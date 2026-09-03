<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import Paginator, { type PageState } from 'primevue/paginator'
import Toolbar from 'primevue/toolbar'
import { useRouter } from 'vue-router'
import { getGroups } from '../../api/groups'
import type { Group, PaginationHeaders } from '../../api/types'
import FilterPanel from '../../components/FilterPanel.vue'
import GroupActionMenu from '../../components/actions/GroupActionMenu.vue'
import { t } from '../../i18n'
import { useAuthStore } from '../../stores/auth'
import {
    debounce,
    groupQuery,
    paginationFromHeaders,
    resetPageOnFilterChange,
} from './groupModels'

const groups = ref<Group[]>([])
const name = ref('')
const loading = ref(false)
const error = ref('')
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    total: 0,
    lastPage: 1,
})
const auth = useAuthStore()
const router = useRouter()
let latestRequest = 0
const debouncedSearch = debounce(
    () => void load(resetPageOnFilterChange(pagination.value.currentPage)),
)

async function load(
    page = 1,
    perPage = pagination.value.perPage,
): Promise<void> {
    const requestId = ++latestRequest
    loading.value = true
    error.value = ''
    try {
        const response = await getGroups(
            groupQuery({ name: name.value, page, perPage }),
        )
        if (requestId !== latestRequest) return
        groups.value = response.data
        pagination.value = paginationFromHeaders(response.headers)
    } catch {
        if (requestId === latestRequest) error.value = t('spa.groups.error')
    } finally {
        if (requestId === latestRequest) loading.value = false
    }
}

function onNameChange(): void {
    if (!name.value.trim()) {
        debouncedSearch.cancel()
        void load(resetPageOnFilterChange(pagination.value.currentPage))
        return
    }
    debouncedSearch()
}
function onPage(event: PageState): void {
    void load(event.page + 1, event.rows)
}
onMounted(() => void load())
onBeforeUnmount(() => debouncedSearch.cancel())
</script>

<template>
    <Toolbar class="page-toolbar"
        ><template #start
            ><h1 class="page-title">{{ t('spa.groups.title') }}</h1></template
        ></Toolbar
    >
    <FilterPanel
        ><div class="filter-field">
            <label for="group-name-filter">{{ t('spa.groups.name') }}</label
            ><InputText
                id="group-name-filter"
                v-model="name"
                @update:model-value="onNameChange"
            /></div
    ></FilterPanel>
    <Message v-if="loading" severity="info" :closable="false">{{
        t('spa.groups.loading')
    }}</Message>
    <Message v-else-if="error" severity="error" :closable="false">{{
        error
    }}</Message>
    <Message
        v-else-if="!groups.length"
        severity="secondary"
        :closable="false"
        >{{ t('spa.groups.empty') }}</Message
    >
    <DataTable v-else :value="groups" striped-rows>
        <Column field="name" :header="t('spa.groups.name')"
            ><template #body="{ data }"
                ><RouterLink :to="`/app/groups/${data.id}`">{{
                    data.name
                }}</RouterLink></template
            ></Column
        >
        <Column
            field="distancesCount"
            :header="t('spa.groups.distances_count')"
        />
        <Column v-if="auth.isAuthenticated" :header="t('spa.group.actions')"
            ><template #body="{ data }"
                ><GroupActionMenu
                    :group-id="data.id"
                    @merge="router.push(`/app/groups/${data.id}/merge`)"
                    @delete="
                        router.push(`/app/groups/${data.id}/edit?delete=1`)
                    " /></template
        ></Column>
    </DataTable>
    <Paginator
        v-if="pagination.total"
        :first="(pagination.currentPage - 1) * pagination.perPage"
        :rows="pagination.perPage"
        :total-records="pagination.total"
        :rows-per-page-options="[10, 20, 50]"
        @page="onPage"
    />
</template>
