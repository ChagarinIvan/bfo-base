<script setup lang="ts">
import { t } from '../../i18n'
import { competitionActionRoute } from './actionModels'
import EditActionButton from './EditActionButton.vue'
import Button from 'primevue/button'

const props = defineProps<{ competitionId: string }>()
const emit = defineEmits<{ delete: [] }>()
</script>

<template>
    <span class="action-menu">
        <EditActionButton
            :to="competitionActionRoute(props.competitionId)"
            :label="t('spa.competition.edit.action')"
        />
        <Button
            icon="pi pi-trash"
            :label="t('spa.competition.delete.action')"
            severity="danger"
            text
            @click="emit('delete')"
        />
    </span>
</template>
