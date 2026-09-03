<script setup lang="ts">
import Button from 'primevue/button'
import { t } from '../../i18n'

const props = defineProps<{ personId: string }>()
const emit = defineEmits<{ delete: [] }>()
</script>

<template>
    <span class="action-menu">
        <Button
            as="a"
            :href="`/persons/${props.personId}/edit`"
            icon="pi pi-pencil"
            :label="t('spa.person.edit')"
            severity="secondary"
            text
        />
        <Button
            icon="pi pi-trash"
            :label="t('spa.person.delete')"
            severity="danger"
            text
            @click="emit('delete')"
        />
    </span>
</template>
