<script setup lang="ts">
import Button from 'primevue/button'
import { useRouter } from 'vue-router'

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
    <Button
        icon="pi pi-pencil"
        :label="props.iconOnly ? undefined : props.label"
        :aria-label="props.iconOnly ? props.label : undefined"
        severity="secondary"
        text
        @click="router.push(props.to)"
    />
</template>
