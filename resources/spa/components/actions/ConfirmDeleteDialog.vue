<script setup lang="ts">
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import { t } from '../../i18n'

defineProps<{
    visible: boolean
    competitionName: string
    pending?: boolean
}>()

const emit = defineEmits<{
    cancel: []
    confirm: []
}>()
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :header="t('spa.competition.delete.title')"
        :closable="!pending"
        @update:visible="emit('cancel')"
    >
        <p>
            {{ t('spa.competition.delete.confirm', { name: competitionName }) }}
        </p>
        <template #footer>
            <Button
                severity="secondary"
                :label="t('spa.competition.delete.cancel')"
                :disabled="pending"
                @click="emit('cancel')"
            />
            <Button
                severity="danger"
                :label="t('spa.competition.delete.action')"
                :loading="pending"
                @click="emit('confirm')"
            />
        </template>
    </Dialog>
</template>
