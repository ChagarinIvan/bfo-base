<script setup lang="ts">
import type { EventProcessingStatus } from '../api/types'

defineProps<{
    status: EventProcessingStatus
}>()

const labels: Record<EventProcessingStatus, string> = {
    parsing: 'Parsing',
    identifying: 'Identifying',
    rebuildingRanks: 'Rebuilding Ranks',
    ready: 'Ready',
    parsingError: 'Parsing Error',
    identifyingError: 'Identification Error',
    rebuildingRanksError: 'Rank Rebuild Error',
}
</script>

<template>
    <span
        :class="['event-processing-badge', `event-processing-badge--${status}`]"
    >
        {{ labels[status] }}
    </span>
</template>
