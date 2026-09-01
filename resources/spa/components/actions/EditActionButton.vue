<script setup lang="ts">
import Button from 'primevue/button'
import { useRouter } from 'vue-router'

const props = defineProps<{
    to: string
    label: string
}>()

const router = useRouter()
</script>

<template>
    <Button
        icon="pi pi-pencil"
        :label="props.label"
        severity="secondary"
        text
        @click="router.push(props.to)"
    />
</template>
