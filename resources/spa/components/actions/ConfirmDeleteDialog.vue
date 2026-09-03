<script setup lang="ts">
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'

const props = withDefaults(
    defineProps<{
        visible: boolean
        title: string
        confirmation: string
        cancelLabel: string
        actionLabel: string
        pending?: boolean
        actionSeverity?: 'danger' | 'success'
    }>(),
    { actionSeverity: 'danger' },
)

const emit = defineEmits<{
    cancel: []
    confirm: []
}>()
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :header="title"
        :closable="!pending"
        @update:visible="emit('cancel')"
    >
        <p>{{ confirmation }}</p>
        <template #footer>
            <Button
                severity="secondary"
                :label="cancelLabel"
                :disabled="pending"
                @click="emit('cancel')"
            />
            <Button
                :severity="props.actionSeverity"
                :label="actionLabel"
                :loading="pending"
                @click="emit('confirm')"
            />
        </template>
    </Dialog>
</template>
