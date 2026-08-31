<script setup lang="ts">
import Button from 'primevue/button'
import { useRouter } from 'vue-router'
import { t } from '../../i18n'
import { competitionActionRoute } from './actionModels'

const props = defineProps<{ competitionId: string }>()
const emit = defineEmits<{ delete: [] }>()
const router = useRouter()
</script>

<template>
    <span class="competition-action-menu">
        <Button
            icon="pi pi-pencil"
            :label="t('spa.competition.edit.action')"
            severity="secondary"
            text
            @click="router.push(competitionActionRoute(props.competitionId))"
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
