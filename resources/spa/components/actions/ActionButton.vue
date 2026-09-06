<script setup lang="ts">
import Button from 'primevue/button'

withDefaults(
    defineProps<{
        label?: string
        icon?: string
        severity?: string
        as?: 'a' | 'button'
        href?: string
        ariaLabel?: string
        disabled?: boolean
        loading?: boolean
    }>(),
    {
        label: undefined,
        icon: undefined,
        severity: 'secondary',
        as: 'button',
        href: undefined,
        ariaLabel: undefined,
        disabled: false,
        loading: false,
    },
)

defineEmits<{ click: [event?: globalThis.MouseEvent] }>()
</script>

<template>
    <Button
        :as="as"
        :href="href"
        :label="label"
        :icon="icon"
        :severity="severity"
        :aria-label="ariaLabel"
        :disabled="disabled"
        :loading="loading"
        text
        class="action-button"
        @click="$emit('click', $event)"
    />
</template>
