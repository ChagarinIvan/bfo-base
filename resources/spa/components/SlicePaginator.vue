<script setup lang="ts">
import Button from 'primevue/button'
import Select from 'primevue/select'
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
    <nav v-if="pagination" class="slice-paginator">
        <Button
            icon="pi pi-chevron-left"
            severity="secondary"
            text
            rounded
            aria-label="Previous page"
            :disabled="pagination.currentPage <= 1"
            @click="go(pagination.currentPage - 2)"
        />
        <span class="slice-paginator__page" aria-current="page">
            {{ pagination.currentPage }}
        </span>
        <Button
            icon="pi pi-chevron-right"
            severity="secondary"
            text
            rounded
            aria-label="Next page"
            :disabled="!pagination.hasNext"
            @click="go(pagination.currentPage)"
        />
        <Select
            :model-value="pagination.perPage"
            :options="rowsPerPageOptions"
            @update:model-value="go(0, $event)"
        />
    </nav>
</template>

<style scoped>
.slice-paginator {
    align-items: center;
    display: flex;
    gap: 0.25rem;
    justify-content: center;
    margin-top: 1rem;
}

.slice-paginator__page {
    align-items: center;
    background: var(--p-primary-color);
    border-radius: var(--p-paginator-nav-button-border-radius);
    color: var(--p-primary-contrast-color);
    display: inline-flex;
    min-width: 2.5rem;
    min-height: var(--p-paginator-nav-button-height);
    text-align: center;
    justify-content: center;
}
</style>
