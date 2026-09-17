<script setup lang="ts">
import { onMounted, ref } from 'vue'
import Button from 'primevue/button'
import Toolbar from 'primevue/toolbar'
import type { PageState } from 'primevue/paginator'
import { useRouter } from 'vue-router'
import { getRankChecks } from '../../api/rankChecks'
import type { PaginationHeaders } from '../../api/types'
import ImpressionDetails from '../../components/ImpressionDetails.vue'
import ListingTable from '../../components/ListingTable.vue'
import { getUsers } from '../../api/users'
import { paginationFromHeaders } from '../listingModels'
import { t } from '../../i18n'
import type { RankCheck } from '../../api/rankChecks'

const router = useRouter()
const checks = ref<RankCheck[]>([])
const users = ref<Awaited<ReturnType<typeof getUsers>>>([])
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    total: 0,
    lastPage: 1,
})
const loading = ref(false)
const error = ref('')

const columns = [
    { key: 'id', label: t('spa.rank_check.number'), defaultVisible: true },
    { key: 'status', label: t('spa.rank_check.status'), defaultVisible: true },
    {
        key: 'created',
        label: t('spa.rank_check.created'),
        defaultVisible: true,
    },
    {
        key: 'updated',
        label: t('spa.rank_check.updated'),
        defaultVisible: true,
    },
]

function statusLabel(status: RankCheck['status']): string {
    return t(`spa.rank_check.status_${status.toLowerCase()}` as never)
}

async function load(
    page = 1,
    perPage = pagination.value.perPage,
): Promise<void> {
    loading.value = true
    error.value = ''
    try {
        const response = await getRankChecks(page, perPage)
        checks.value = response.data
        pagination.value = paginationFromHeaders(response.headers)
        if (!users.value.length) users.value = await getUsers()
    } catch {
        error.value = t('spa.rank_check.list_error')
    } finally {
        loading.value = false
    }
}

async function onPage(event: PageState): Promise<void> {
    await load(event.page + 1, event.rows)
}

onMounted(() => void load())
</script>

<template>
    <Toolbar class="page-toolbar">
        <template #start>
            <h1 class="page-title">{{ t('spa.rank_check.title') }}</h1>
        </template>
        <template #end>
            <Button
                icon="pi pi-plus"
                :label="t('spa.rank_check.create')"
                severity="success"
                @click="router.push('/app/rank-checks/create')"
            />
        </template>
    </Toolbar>

    <ListingTable
        table-id="rank-checks"
        :columns="columns"
        authenticated
        :items="checks"
        :pagination="pagination"
        :loading="loading"
        :error="error"
        :loading-label="t('spa.rank_check.loading')"
        :empty-label="t('spa.rank_check.empty')"
        table-class="rank-checks-table"
        @page="onPage"
    >
        <template #cell-id="{ data }">
            <RouterLink :to="`/app/rank-checks/${data.id}`">
                #{{ data.id }}
            </RouterLink>
        </template>
        <template #cell-status="{ data }">
            {{ statusLabel(data.status) }}
        </template>
        <template #cell-created="{ data }">
            <ImpressionDetails
                :impression="data.created"
                :users="users"
                :label="t('spa.rank_check.created')"
            />
        </template>
        <template #cell-updated="{ data }">
            <ImpressionDetails
                :impression="data.updated"
                :users="users"
                :label="t('spa.rank_check.updated')"
            />
        </template>
    </ListingTable>
</template>
