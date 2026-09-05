<script setup lang="ts">
import Button from 'primevue/button'
import { t } from '../../i18n'
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
            :label="t('spa.person_prompt.edit')"
            icon-only
        />
        <Button
            icon="pi pi-trash"
            :aria-label="t('spa.person_prompt.delete')"
            severity="danger"
            text
            @click="emit('delete')"
        />
    </span>
</template>
