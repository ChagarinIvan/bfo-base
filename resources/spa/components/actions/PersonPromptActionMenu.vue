<script setup lang="ts">
import { t } from '../../i18n'
import ActionButton from './ActionButton.vue'
import EditActionButton from './EditActionButton.vue'

const props = defineProps<{
    personId: string
    promptId: string
}>()

const emit = defineEmits<{ delete: [] }>()
</script>

<template>
    <span class="action-menu">
        <EditActionButton
            :to="`/app/persons/${props.personId}/prompts/${props.promptId}/edit`"
            :label="t('app.common.edit')"
        />
        <ActionButton
            icon="pi pi-trash"
            :label="t('spa.person_prompt.delete')"
            severity="danger"
            @click="emit('delete')"
        />
    </span>
</template>
