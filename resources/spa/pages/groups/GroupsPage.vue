<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'
import type { PageState } from 'primevue/paginator'
import Toolbar from 'primevue/toolbar'
import { useRouter } from 'vue-router'
import { deleteGroup, getGroups } from '../../api/groups'
import type { Group, PaginationHeaders } from '../../api/types'
import GroupActionMenu from '../../components/actions/GroupActionMenu.vue'
import ConfirmDeleteDialog from '../../components/actions/ConfirmDeleteDialog.vue'
import GroupListingTable from '../../components/GroupListingTable.vue'
import { t } from '../../i18n'
import { useAuthStore } from '../../stores/auth'
import {
    debounce,
    groupQuery,
    paginationFromHeaders,
    resetPageOnFilterChange,
} from './groupModels'

const groups = ref<Group[]>([])
const name = ref('')
const loading = ref(false)
const error = ref('')
const selectedGroup = ref<Group | null>(null)
const deleting = ref(false)
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    total: 0,
    lastPage: 1,
})
const auth = useAuthStore()
const router = useRouter()
let latestRequest = 0
const debouncedSearch = debounce(
    () => void load(resetPageOnFilterChange(pagination.value.currentPage)),
)

async function load(
    page = 1,
    perPage = pagination.value.perPage,
): Promise<void> {
    const requestId = ++latestRequest
    loading.value = true
    error.value = ''
    try {
        const response = await getGroups(
            groupQuery({ name: name.value, page, perPage }),
        )
        if (requestId !== latestRequest) return
        groups.value = response.data
        pagination.value = paginationFromHeaders(response.headers)
    } catch {
        if (requestId === latestRequest) error.value = t('spa.groups.error')
    } finally {
        if (requestId === latestRequest) loading.value = false
    }
}

function onNameChange(): void {
    if (!name.value.trim()) {
        debouncedSearch.cancel()
        void load(resetPageOnFilterChange(pagination.value.currentPage))
        return
    }
    debouncedSearch()
}
function onPage(event: PageState): void {
    void load(event.page + 1, event.rows)
}
async function remove(): Promise<void> {
    if (!selectedGroup.value) return

    deleting.value = true
    try {
        await deleteGroup(selectedGroup.value.id)
        await load(pagination.value.currentPage, pagination.value.perPage)
    } catch {
        error.value = t('spa.group.delete.error')
    } finally {
        deleting.value = false
        selectedGroup.value = null
    }
}
onMounted(() => void load())
onBeforeUnmount(() => debouncedSearch.cancel())
</script>

<template>
    <Toolbar class="page-toolbar"
        ><template #start
            ><h1 class="page-title">{{ t('spa.groups.title') }}</h1></template
        ></Toolbar
    >
    <GroupListingTable
        :groups="groups"
        :name="name"
        :loading="loading"
        :error="error"
        :pagination="pagination"
        :show-actions="auth.isAuthenticated"
        @update:name="name = $event"
        @search="onNameChange"
        @page="onPage"
    >
        <template #actions="{ group }">
            <GroupActionMenu
                v-if="auth.isAuthenticated"
                :group-id="group.id"
                @merge="router.push(`/app/groups/${group.id}/merge`)"
                @delete="selectedGroup = group"
            />
        </template>
    </GroupListingTable>
    <ConfirmDeleteDialog
        :visible="selectedGroup !== null"
        :title="t('spa.group.delete')"
        :confirmation="t('spa.group.delete.confirm')"
        :cancel-label="t('spa.common.cancel')"
        :action-label="t('spa.group.delete')"
        :pending="deleting"
        @cancel="selectedGroup = null"
        @confirm="remove"
    />
</template>
