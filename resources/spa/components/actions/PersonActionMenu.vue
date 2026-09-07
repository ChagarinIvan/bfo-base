<script setup lang="ts">
import { t } from '../../i18n'
import ActionButton from './ActionButton.vue'

const props = defineProps<{ personId: string }>()
const emit = defineEmits<{ delete: [] }>()
</script>

<template>
    <span class="action-menu">
        <ActionButton
            as="a"
            :href="`/app/persons/${props.personId}/edit`"
            icon="pi pi-pencil"
            :label="t('spa.person.edit')"
            severity="secondary"
        />
        <ActionButton
            icon="pi pi-trash"
            :label="t('spa.person.delete')"
            severity="danger"
            @click="emit('delete')"
        />
    </span>
</template>
