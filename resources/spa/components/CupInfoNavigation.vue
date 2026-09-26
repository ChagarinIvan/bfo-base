<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { t } from '../i18n'
import ActionButton from './actions/ActionButton.vue'
import { exportCupTable } from '../api/cups'

const props = defineProps<{ cupId: string; firstGroupId?: string }>()
const route = useRoute()
const auth = useAuthStore()
const emit = defineEmits<{ deleteCup: [] }>()
const selectedGroupId = computed(() =>
    String(
        route.params.groupId ?? route.query.groupId ?? props.firstGroupId ?? '',
    ),
)
const tableUrl = computed(
    () => `/app/cups/${props.cupId}/table/${selectedGroupId.value}`,
)
const eventsUrl = computed(() =>
    route.params.groupId
        ? {
              path: `/app/cups/${props.cupId}`,
              query: { groupId: selectedGroupId.value },
          }
        : `/app/cups/${props.cupId}`,
)

async function downloadCupTable() {
    const response = await exportCupTable(props.cupId)
    const url = URL.createObjectURL(response.data)
    const link = document.createElement('a')
    link.href = url
    link.download = `cup-${props.cupId}.csv`
    document.body.append(link)
    link.click()
    link.remove()
    URL.revokeObjectURL(url)
}
</script>

<template>
    <div class="details-actions">
        <ActionButton
            v-if="auth.isAuthenticated"
            as="a"
            :href="`/app/cups/${props.cupId}/edit`"
            icon="pi pi-pencil"
            :label="t('spa.cups.edit.action')"
        />
        <ActionButton
            v-if="auth.isAuthenticated"
            as="a"
            :href="`/app/cups/${props.cupId}/events/create`"
            icon="pi pi-plus"
            :label="t('app.competition.add_event')"
            severity="success"
        />
        <RouterLink v-slot="{ navigate, isExactActive }" :to="eventsUrl" custom>
            <ActionButton
                type="button"
                :class="{ 'person-info-tab-active': isExactActive }"
                icon="pi pi-list"
                :label="t('app.cup.events')"
                @click="navigate"
            />
        </RouterLink>
        <RouterLink
            v-if="selectedGroupId !== ''"
            v-slot="{ navigate, isActive }"
            :to="tableUrl"
            custom
        >
            <ActionButton
                type="button"
                :class="{ 'person-info-tab-active': isActive }"
                icon="pi pi-table"
                :label="t('spa.cups.table')"
                @click="navigate"
            />
        </RouterLink>
        <ActionButton
            v-if="auth.isAuthenticated"
            type="button"
            icon="pi pi-download"
            :label="t('app.cup.table.export')"
            severity="info"
            @click="downloadCupTable"
        />
        <ActionButton
            v-if="auth.isAuthenticated"
            as="a"
            :href="`/cups/${props.cupId}/cache`"
            icon="pi pi-refresh"
            :label="t('app.common.cache_clear')"
            severity="warn"
        />
        <ActionButton
            v-if="auth.isAuthenticated"
            type="button"
            icon="pi pi-trash"
            :label="t('spa.cups.delete.action')"
            severity="danger"
            @click="emit('deleteCup')"
        />
    </div>
</template>
