<script setup lang="ts">
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import Paginator, { type PageState } from 'primevue/paginator'
import type { Group, PaginationHeaders } from '../api/types'
import { t } from '../i18n'
import FilterPanel from './FilterPanel.vue'

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
</script>

<template>
    <FilterPanel>
        <div class="filter-field">
            <label for="group-name-filter">{{ t('spa.groups.name') }}</label>
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
    <DataTable v-else :value="groups" striped-rows class="groups-table">
        <Column field="name" :header="t('spa.groups.name')">
            <template #body="{ data }">
                <RouterLink :to="`/app/groups/${data.id}`">
                    {{ data.name }}
                </RouterLink>
            </template>
        </Column>
        <Column
            field="distancesCount"
            :header="t('spa.groups.distances_count')"
        />
        <Column v-if="showActions" :header="t('spa.group.actions')">
            <template #body="{ data }">
                <slot name="actions" :group="data" />
            </template>
        </Column>
    </DataTable>
    <Paginator
        v-if="pagination.total > 0"
        :first="(pagination.currentPage - 1) * pagination.perPage"
        :rows="pagination.perPage"
        :total-records="pagination.total"
        :rows-per-page-options="[10, 20, 50]"
        class="groups-paginator"
        @page="(event) => emit('page', event)"
    />
</template>
