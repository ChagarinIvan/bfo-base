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
    <span class="cup-type-icon" :title="label">
        <i :class="definition.icon" :aria-label="label" role="img" />
        <span v-if="showLabel" class="cup-type-icon__label">{{ label }}</span>
    </span>
</template>
