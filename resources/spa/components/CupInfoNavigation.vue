<script setup lang="ts">
import { computed, ref } from 'vue'
import Message from 'primevue/message'
import Menu from 'primevue/menu'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { t } from '../i18n'
import ActionButton from './actions/ActionButton.vue'
import { clearCupCache, exportCupTable } from '../api/cups'

const props = defineProps<{ cupId: string; firstGroupId?: string }>()
const route = useRoute()
const auth = useAuthStore()
const error = ref('')
const exportLoading = ref(false)
const exportMenu = ref<InstanceType<typeof Menu> | null>(null)
const emit = defineEmits<{ deleteCup: [] }>()
const exportItems = [
    {
        label: t('app.cup.table.export.csv'),
        command: () => void downloadCupTable('csv'),
    },
    {
        label: t('app.cup.table.export.html'),
        command: () => void downloadCupTable('html'),
    },
]
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

function toggleExportMenu(event?: globalThis.MouseEvent): void {
    if (event && !exportLoading.value) {
        exportMenu.value?.toggle(event)
    }
}

async function downloadCupTable(format: 'csv' | 'html') {
    if (exportLoading.value) return
    error.value = ''
    exportLoading.value = true
    try {
        const groupId = route.params.groupId
            ? String(route.params.groupId)
            : undefined
        const response = await exportCupTable(props.cupId, format, groupId)
        const url = URL.createObjectURL(response.data)
        const link = document.createElement('a')
        link.href = url
        link.download = `cup-${props.cupId}${groupId ? `-${groupId}` : ''}.${format}`
        document.body.append(link)
        link.click()
        link.remove()
        URL.revokeObjectURL(url)
    } catch {
        error.value = t('spa.cups.error')
    } finally {
        exportLoading.value = false
    }
}

async function clearCache() {
    error.value = ''
    try {
        await clearCupCache()
        window.location.reload()
    } catch {
        error.value = t('spa.cups.error')
    }
}
</script>

<template>
    <div class="details-actions">
        <Message v-if="error" severity="error" :closable="false">{{
            error
        }}</Message>
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
            :disabled="exportLoading"
            :loading="exportLoading"
            aria-haspopup="menu"
            aria-controls="cup-export-menu"
            @click="toggleExportMenu"
        />
        <Menu
            v-if="auth.isAuthenticated"
            id="cup-export-menu"
            ref="exportMenu"
            :model="exportItems"
            popup
        />
        <ActionButton
            v-if="auth.isAuthenticated"
            type="button"
            icon="pi pi-refresh"
            :label="t('app.common.cache_clear')"
            severity="warn"
            @click="clearCache"
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
