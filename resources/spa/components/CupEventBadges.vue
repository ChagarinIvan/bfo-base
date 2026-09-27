<script setup lang="ts">
import { computed, ref } from 'vue'
import Popover from 'primevue/popover'
import type { CupEventContext, CupGroup } from '../api/types'
import { cupGroupBadgeClass } from '../pages/cups/cupModels'
import CupTypeIcon from './CupTypeIcon.vue'

const props = defineProps<{
    contexts: CupEventContext[]
    groupName?: string | null
}>()

type BadgeContext = CupEventContext & { badgeGroup?: CupGroup }

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

const visibleContexts = computed<BadgeContext[]>(() =>
    props.contexts.flatMap((context) => {
        if (!props.groupName) return [context]

        const group = context.groups.find(
            (item) => item.name === props.groupName,
        )
        if (!group) return []

        return [
            {
                ...context,
                badgeGroup: group,
            },
        ]
    }),
)
</script>

<template>
    <span v-if="visibleContexts.length" class="cup-event-badges">
        <a
            v-for="context in visibleContexts"
            :key="context.cupEventId"
            :href="context.href"
            :class="[
                'cup-event-badge',
                context.badgeGroup
                    ? cupGroupBadgeClass(context.badgeGroup)
                    : 'cup-event-badge--green',
            ]"
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
