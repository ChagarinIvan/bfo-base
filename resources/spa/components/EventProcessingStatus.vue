<script setup lang="ts">
import { computed } from 'vue'
import Message from 'primevue/message'
import ProgressSpinner from 'primevue/progressspinner'
import { t, type TranslationKey } from '../i18n'
import type { EventProcessingStatus } from '../api/types'

const props = defineProps<{
    status: EventProcessingStatus
    errorMessage?: string | null
    refreshError?: boolean
}>()

const messages: Record<EventProcessingStatus, TranslationKey> = {
    parsing: 'spa.event.processing.parsing',
    identifying: 'spa.event.processing.identifying',
    rebuildingRanks: 'spa.event.processing.rebuildingRanks',
    ready: 'spa.event.processing.ready',
    parsingError: 'spa.event.processing.parsingError',
    identifyingError: 'spa.event.processing.identifyingError',
    rebuildingRanksError: 'spa.event.processing.rebuildingRanksError',
}

const waiting = computed(() =>
    ['parsing', 'identifying', 'rebuildingRanks'].includes(props.status),
)
const severity = computed(() => {
    if (props.status.endsWith('Error')) return 'error'
    if (props.status === 'identifying') return 'warn'

    return 'info'
})
</script>

<template>
    <Message
        v-if="status !== 'ready'"
        :severity="severity"
        :closable="false"
        class="mt-3"
    >
        <ProgressSpinner v-if="waiting" style="width: 1rem; height: 1rem" />
        {{
            status.endsWith('Error') && errorMessage
                ? errorMessage
                : t(messages[status])
        }}
    </Message>
    <Message v-if="refreshError" severity="warn" :closable="false" class="mt-3">
        {{ t('spa.event.processing.refresh_error') }}
    </Message>
</template>
