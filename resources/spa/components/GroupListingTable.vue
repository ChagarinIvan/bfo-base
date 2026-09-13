<script setup lang="ts">
import InputText from 'primevue/inputtext'
import type { PageState } from 'primevue/paginator'
import type { Group, PaginationHeaders } from '../api/types'
import { t } from '../i18n'
import FilterPanel from './FilterPanel.vue'
import ListingTable from './ListingTable.vue'

defineProps<{
    groups: Group[]
    name: string
    loading: boolean
    error: string
    pagination: PaginationHeaders
    showActions: boolean
}>()

const emit = defineEmits<{
    'update:name': [value: string]
    search: []
    page: [event: PageState]
}>()

const columns = [
    { key: 'name', label: t('spa.groups.name'), defaultVisible: true },
    {
        key: 'distances',
        label: t('spa.groups.distances_count'),
        defaultVisible: true,
        field: 'distancesCount',
    },
    { key: 'actions', label: t('spa.group.actions'), defaultVisible: true },
]
</script>

<template>
    <ListingTable
        table-id="groups"
        :columns="showActions ? columns : columns.slice(0, 2)"
        :authenticated="showActions"
    >
        <template #filters>
            <FilterPanel>
                <div class="filter-field">
                    <label for="group-name-filter">{{
                        t('spa.groups.name')
                    }}</label>
                    <InputText
                        id="group-name-filter"
                        :model-value="name"
                        @update:model-value="
                            (value) => {
                                emit('update:name', value ?? '')
                                emit('search')
                            }
                        "
                    />
                </div>
            </FilterPanel>
        </template>
        :items="groups" :pagination="pagination" :loading="loading"
        :error="error" :loading-label="t('spa.groups.loading')"
        :empty-label="t('spa.groups.empty')" table-class="groups-table"
        @page="(event) => emit('page', event)" >
        <template #cell-name="{ data }">
            <RouterLink :to="`/app/groups/${data.id}`">
                {{ data.name }}
            </RouterLink>
        </template>
        <template v-if="showActions" #cell-actions="{ data }">
            <slot name="actions" :group="data" />
        </template>
    </ListingTable>
</template>
