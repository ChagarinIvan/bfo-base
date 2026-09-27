<script setup lang="ts">
import { t } from '../../i18n'
import { RouterLink } from 'vue-router'
import ActionButton from './ActionButton.vue'
import EditActionButton from './EditActionButton.vue'

const props = defineProps<{ cupId: string; tableUrl: string }>()
const emit = defineEmits<{ delete: [] }>()
</script>

<template>
    <span class="action-menu">
        <EditActionButton
            :to="`/app/cups/${props.cupId}/edit`"
            :label="t('spa.cups.edit.action')"
        />
        <RouterLink v-slot="{ navigate }" :to="props.tableUrl" custom>
            <ActionButton
                icon="pi pi-table"
                :label="t('spa.cups.table')"
                severity="success"
                @click="navigate"
            />
        </RouterLink>
        <ActionButton
            icon="pi pi-trash"
            :label="t('spa.cups.delete.action')"
            severity="danger"
            @click="emit('delete')"
        />
    </span>
</template>
