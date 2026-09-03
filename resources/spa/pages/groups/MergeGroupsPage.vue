<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'
import Button from 'primevue/button'
import Card from 'primevue/card'
import type { PageState } from 'primevue/paginator'
import Toolbar from 'primevue/toolbar'
import { useRoute, useRouter } from 'vue-router'
import { getGroup, getGroups, mergeGroups } from '../../api/groups'
import type { Group, PaginationHeaders } from '../../api/types'
import type { User } from '../../api/types'
import ConfirmDeleteDialog from '../../components/actions/ConfirmDeleteDialog.vue'
import GroupListingTable from '../../components/GroupListingTable.vue'
import ImpressionDetails from '../../components/ImpressionDetails.vue'
import { getUsers } from '../../api/users'
import { t } from '../../i18n'
import { debounce, groupQuery, paginationFromHeaders } from './groupModels'
const route = useRoute()
const router = useRouter()
const source = ref<Group | null>(null)
const users = ref<User[]>([])
const groups = ref<Group[]>([])
const name = ref('')
const target = ref<Group | null>(null)
const pending = ref(false)
const loading = ref(false)
const error = ref('')
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    total: 0,
    lastPage: 1,
})
const filter = debounce(() => void load())
async function load(
    page = 1,
    perPage = pagination.value.perPage,
): Promise<void> {
    loading.value = true
    try {
        const r = await getGroups(
            groupQuery({
                name: name.value,
                excludeId: String(route.params.id),
                page,
                perPage,
            }),
        )
        groups.value = r.data
        pagination.value = paginationFromHeaders(r.headers)
    } catch {
        error.value = t('spa.group.merge.error')
    } finally {
        loading.value = false
    }
}
async function confirm(): Promise<void> {
    if (!target.value) return
    pending.value = true
    try {
        await mergeGroups(String(route.params.id), target.value.id)
        await router.push('/app/groups')
    } catch {
        error.value = t('spa.group.merge.error')
    } finally {
        pending.value = false
    }
}
onMounted(async () => {
    try {
        source.value = await getGroup(String(route.params.id))
        users.value = await getUsers()
        await load()
    } catch {
        error.value = t('spa.group.details.error')
    }
})
onBeforeUnmount(() => filter.cancel())
</script>
<template>
    <Toolbar class="page-toolbar">
        <template #start>
            <div>
                <h1 class="page-title">{{ t('spa.group.merge') }}</h1>
                <p v-if="source" class="page-subtitle">{{ source.name }}</p>
            </div>
        </template>
    </Toolbar>
    <Card v-if="source" class="group-details-card">
        <template #title>{{ source.name }}</template>
        <template #content>
            <table class="group-details-info">
                <tbody>
                    <tr>
                        <th scope="row">
                            {{ t('spa.groups.distances_count') }}
                        </th>
                        <td>{{ source.distancesCount }}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ t('spa.groups.created') }}</th>
                        <td>
                            <ImpressionDetails
                                :impression="source.created"
                                :users="users"
                                :label="t('spa.groups.created')"
                            />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">{{ t('spa.groups.updated') }}</th>
                        <td>
                            <ImpressionDetails
                                :impression="source.updated"
                                :users="users"
                                :label="t('spa.groups.updated')"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </template>
    </Card>
    <GroupListingTable
        :groups="groups"
        :name="name"
        :loading="loading"
        :error="error"
        :pagination="pagination"
        show-actions
        @update:name="name = $event"
        @search="filter"
        @page="(event: PageState) => load(event.page + 1, event.rows)"
    >
        <template #actions="{ group }">
            <Button
                icon="pi pi-objects-column"
                :label="t('spa.group.merge')"
                severity="success"
                text
                @click="target = group"
            />
        </template>
    </GroupListingTable>
    <ConfirmDeleteDialog
        :visible="Boolean(target)"
        :title="t('spa.group.merge')"
        :confirmation="
            t('spa.group.merge.confirm', {
                source: source?.name ?? '',
                target: target?.name ?? '',
            })
        "
        :cancel-label="t('spa.common.cancel')"
        :action-label="t('spa.group.merge')"
        action-severity="success"
        :pending="pending"
        @cancel="target = null"
        @confirm="confirm"
    />
</template>
