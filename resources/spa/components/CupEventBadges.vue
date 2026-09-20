<script setup lang="ts">
import { ref } from 'vue'
import Popover from 'primevue/popover'
import type { CupEventContext } from '../api/types'
import CupTypeIcon from './CupTypeIcon.vue'

defineProps<{ contexts: CupEventContext[] }>()

const selected = ref<CupEventContext | null>(null)
const popover = ref<{
    show: (event: unknown) => void
    hide: () => void
} | null>(null)

function show(event: unknown, context: CupEventContext): void {
    selected.value = context
    popover.value?.show(event)
}

function hide(): void {
    popover.value?.hide()
}
</script>

<template>
    <span v-if="contexts.length" class="cup-event-badges">
        <a
            v-for="context in contexts"
            :key="context.cupEventId"
            :href="context.href"
            class="cup-event-badge"
            :aria-label="context.cupName"
            @mouseenter="show($event, context)"
            @mouseleave="hide"
            @focus="show($event, context)"
            @blur="hide"
        >
            <CupTypeIcon :type="context.cupType" />
        </a>
        <Popover ref="popover">
            <span>{{ selected?.cupName }}</span>
        </Popover>
    </span>
</template>
