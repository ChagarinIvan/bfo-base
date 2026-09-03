<script setup lang="ts">
import Button from 'primevue/button'
import EditActionButton from './EditActionButton.vue'
import { t } from '../../i18n'

defineProps<{ groupId: string }>()
const emit = defineEmits<{ delete: []; merge: [] }>()
</script>

<template>
    <span class="action-menu">
        <EditActionButton
            :to="`/app/groups/${groupId}/edit`"
            :label="t('spa.group.edit')"
        />
        <Button
            icon="pi pi-object-group"
            text
            :label="t('spa.group.merge')"
            @click="emit('merge')"
        />
        <Button
            icon="pi pi-trash"
            severity="danger"
            text
            :label="t('spa.group.delete')"
            @click="emit('delete')"
        />
    </span>
</template>
