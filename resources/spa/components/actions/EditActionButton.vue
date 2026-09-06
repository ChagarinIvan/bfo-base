<script setup lang="ts">
import { useRouter } from 'vue-router'
import ActionButton from './ActionButton.vue'

const props = withDefaults(
    defineProps<{
        to: string
        label: string
        iconOnly?: boolean
    }>(),
    { iconOnly: false },
)

const router = useRouter()
</script>

<template>
    <ActionButton
        icon="pi pi-pencil"
        :label="props.iconOnly ? undefined : props.label"
        :aria-label="props.iconOnly ? props.label : undefined"
        severity="secondary"
        @click="router.push(props.to)"
    />
</template>
