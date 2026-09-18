<script setup lang="ts">
import Paginator from 'primevue/paginator'
import type { PageState } from 'primevue/paginator'
import type { PaginationHeaders } from '../api/types'

const props = withDefaults(
    defineProps<{
        pagination: PaginationHeaders
        rowsPerPageOptions?: number[]
    }>(),
    { rowsPerPageOptions: () => [10, 20, 50] },
)

const emit = defineEmits<{ page: [event: PageState] }>()

function syntheticTotalRecords(): number {
    const visiblePages = props.pagination.currentPage +
        (props.pagination.hasNext ? 1 : 0)

    return props.pagination.perPage * visiblePages
}
</script>

<template>
    <Paginator
        v-if="pagination"
        :first="(pagination.currentPage - 1) * pagination.perPage"
        :rows="pagination.perPage"
        :total-records="syntheticTotalRecords()"
        :rows-per-page-options="rowsPerPageOptions"
        @page="emit('page', $event)"
    />
</template>
