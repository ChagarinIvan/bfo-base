<script setup lang="ts">
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

function go(page: number, rows = props.pagination.perPage): void {
    emit('page', {
        page,
        first: page * rows,
        rows,
    })
}
</script>

<template>
    <nav v-if="pagination.currentPage > 1 || pagination.hasNext" class="slice-paginator">
        <button :disabled="pagination.currentPage <= 1" @click="go(pagination.currentPage - 2)">
            Previous
        </button>
        <span>Page {{ pagination.currentPage }}</span>
        <button :disabled="!pagination.hasNext" @click="go(pagination.currentPage)">
            Next
        </button>
        <select :value="pagination.perPage" @change="go(0, Number(($event.target as HTMLSelectElement).value))">
            <option v-for="option in rowsPerPageOptions" :key="option" :value="option">
                {{ option }}
            </option>
        </select>
    </nav>
</template>
