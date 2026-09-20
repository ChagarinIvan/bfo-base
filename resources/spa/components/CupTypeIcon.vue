<script setup lang="ts">
import { computed } from 'vue'
import { t } from '../i18n'
import { cupTypeDefinition } from './cupTypeModels'

const props = withDefaults(
    defineProps<{ type: string; showLabel?: boolean }>(),
    { showLabel: false },
)

const definition = computed(() => cupTypeDefinition(props.type))
const label = computed(() => t(definition.value.label))
</script>

<template>
    <span
        :class="['cup-type-icon', { 'cup-type-icon--standalone': !showLabel }]"
        :title="label"
    >
        <svg
            v-if="definition.illustration === 'moose'"
            class="cup-type-icon__svg"
            viewBox="0 0 64 48"
            :aria-label="label"
            role="img"
        >
            <path
                d="M8 31c2-6 9-9 18-9h9c4 0 7-2 9-6l2-5 3 1-1 6 4-4 2 2-4 6c4 1 6 4 6 7 0 4-3 7-7 7h-2v6h-3v-6H25v6h-3v-6h-7v6h-3v-7c-3-1-4-3-4-5Z"
                fill="currentColor"
            />
            <path
                d="M48 12 45 5m3 7 5-5m-5 5-1-7m4 11 5-2"
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-width="2.5"
            />
        </svg>
        <i v-else :class="definition.icon" :aria-label="label" role="img" />
        <span v-if="showLabel" class="cup-type-icon__label">{{ label }}</span>
    </span>
</template>
