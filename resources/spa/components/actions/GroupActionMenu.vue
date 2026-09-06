<script setup lang="ts">
import EditActionButton from './EditActionButton.vue'
import ActionButton from './ActionButton.vue'
import { t } from '../../i18n'

const props = withDefaults(
    defineProps<{ groupId: string; layout?: 'row' | 'column' }>(),
    { layout: 'column' },
)
const emit = defineEmits<{ delete: []; merge: [] }>()
</script>

<template>
    <span
        class="action-menu"
        :class="{ 'details-actions': props.layout === 'row' }"
    >
        <EditActionButton
            :to="`/app/groups/${props.groupId}/edit`"
            :label="t('spa.group.edit')"
        />
        <ActionButton
            icon="pi pi-objects-column"
            severity="success"
            :label="t('spa.group.merge')"
            @click="emit('merge')"
        />
        <ActionButton
            icon="pi pi-trash"
            severity="danger"
            :label="t('spa.group.delete')"
            @click="emit('delete')"
        />
    </span>
</template>
