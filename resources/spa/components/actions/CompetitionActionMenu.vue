<script setup lang="ts">
import { t } from '../../i18n'
import { competitionActionRoute } from './actionModels'
import EditActionButton from './EditActionButton.vue'
import ActionButton from './ActionButton.vue'

const props = withDefaults(
    defineProps<{ competitionId: string; layout?: 'row' | 'column' }>(),
    { layout: 'column' },
)
const emit = defineEmits<{ delete: [] }>()
</script>

<template>
    <span
        class="action-menu"
        :class="{ 'details-actions': props.layout === 'row' }"
    >
        <EditActionButton
            :to="competitionActionRoute(props.competitionId)"
            :label="t('spa.competition.edit.action')"
        />
        <slot name="between" />
        <ActionButton
            icon="pi pi-trash"
            :label="t('spa.competition.delete.action')"
            severity="danger"
            @click="emit('delete')"
        />
    </span>
</template>
