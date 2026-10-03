<script setup lang="ts">
import { computed } from 'vue'
import Message from 'primevue/message'
import ProgressSpinner from 'primevue/progressspinner'
import { t, type TranslationKey } from '../i18n'
import type { EventProcessingStatus } from '../api/types'

const props = defineProps<{
    status: EventProcessingStatus
    refreshError?: boolean
}>()

const messages: Record<EventProcessingStatus, TranslationKey> = {
    queued: 'spa.event.processing.queued',
    parsing: 'spa.event.processing.parsing',
    identifying: 'spa.event.processing.identifying',
    rebuildingRanks: 'spa.event.processing.rebuildingRanks',
    ready: 'spa.event.processing.ready',
    failed: 'spa.event.processing.failed',
}

const waiting = computed(() => !['ready', 'failed'].includes(props.status))
const severity = computed(() => {
    if (props.status === 'failed') return 'error'
    if (props.status === 'identifying') return 'warn'

    return 'info'
})
</script>

<template>
    <Message :severity="severity" :closable="false" class="mt-3">
        <ProgressSpinner v-if="waiting" style="width: 1rem; height: 1rem" />
        {{ t(messages[status]) }}
    </Message>
    <Message
        v-if="refreshError && waiting"
        severity="warn"
        :closable="false"
        class="mt-3"
    >
        {{ t('spa.event.processing.refresh_error') }}
    </Message>
</template>
