<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import Message from 'primevue/message'
import type { PageState } from 'primevue/paginator'
import type { ListingColumn } from './tableModels'
import type { PaginationHeaders } from '../api/types'
import { sanitizeVisibleColumns, tableStorageKey } from './tableModels'
import SlicePaginator from './SlicePaginator.vue'

const props = withDefaults(
    defineProps<{
        tableId: string
        columns: ListingColumn[]
        authenticated?: boolean
        items?: unknown[]
        pagination?: PaginationHeaders
        loading?: boolean
        error?: string
        loadingLabel?: string
        emptyLabel?: string
        tableClass?: string
        rowsPerPageOptions?: number[]
    }>(),
    {
        authenticated: false,
        items: undefined,
        pagination: undefined,
        loading: false,
        error: '',
        loadingLabel: '',
        emptyLabel: '',
        tableClass: '',
        rowsPerPageOptions: () => [10, 20, 50],
    },
)

const emit = defineEmits<{ page: [event: PageState] }>()

const storageKey = computed(() =>
    tableStorageKey(props.tableId, props.authenticated),
)
const availableKeys = computed(() => props.columns.map((column) => column.key))
const defaultKeys = computed(() =>
    props.columns
        .filter((column) => column.defaultVisible)
        .map((column) => column.key),
)
const visible = ref<string[]>([])

function restore(): void {
    try {
        const raw = window.localStorage.getItem(storageKey.value)
        const saved: string[] = raw === null ? [] : JSON.parse(raw)
        visible.value = sanitizeVisibleColumns(
            Array.isArray(saved) ? saved : [],
            availableKeys.value,
            defaultKeys.value,
        )
    } catch {
        visible.value = [...defaultKeys.value]
    }
}

function isVisible(key: string): boolean {
    return visible.value.includes(key)
}

function toggle(key: string, checked: boolean): void {
    if (!checked && visible.value.length === 1 && isVisible(key)) return

    visible.value = checked
        ? [...visible.value, key]
        : visible.value.filter((visibleKey) => visibleKey !== key)
}

watch(storageKey, restore, { immediate: true })
watch(visible, (value) => {
    window.localStorage.setItem(storageKey.value, JSON.stringify(value))
})
</script>

<template>
    <div
        class="filter-card listing-table__controls listing-table__filters--sticky"
    >
        <section
            class="listing-table__columns"
            :class="{ 'listing-table__columns--with-filters': $slots.filters }"
        >
            <div class="listing-table__column-options">
                <label v-for="column in columns" :key="column.key">
                    <input
                        type="checkbox"
                        :checked="isVisible(column.key)"
                        :disabled="
                            visible.length === 1 && isVisible(column.key)
                        "
                        @change="
                            toggle(
                                column.key,
                                ($event.target as HTMLInputElement).checked,
                            )
                        "
                    />
                    {{ column.label }}
                </label>
            </div>
        </section>
        <div v-if="$slots.filters">
            <slot name="filters" />
        </div>
    </div>
    <template v-if="items !== undefined">
        <Message
            v-if="loading"
            class="listing-table__message"
            severity="info"
            :closable="false"
        >
            {{ loadingLabel }}
        </Message>
        <Message
            v-else-if="error"
            class="listing-table__message"
            severity="error"
            :closable="false"
        >
            {{ error }}
        </Message>
        <Message
            v-else-if="items.length === 0 && emptyLabel"
            class="listing-table__message"
            severity="secondary"
            :closable="false"
        >
            {{ emptyLabel }}
        </Message>
        <DataTable
            v-else-if="items.length"
            :value="items"
            striped-rows
            :class="tableClass"
        >
            <Column
                v-for="column in columns.filter((item) => isVisible(item.key))"
                :key="column.key"
                :field="column.field"
                :header="column.label"
            >
                <template v-if="$slots[`cell-${column.key}`]" #body="slotProps">
                    <slot :name="`cell-${column.key}`" v-bind="slotProps" />
                </template>
            </Column>
        </DataTable>
        <SlicePaginator
            v-if="pagination"
            :pagination="pagination"
            :rows-per-page-options="rowsPerPageOptions"
            @page="(event) => emit('page', event)"
        />
    </template>
    <slot v-else :is-visible="isVisible" />
</template>
