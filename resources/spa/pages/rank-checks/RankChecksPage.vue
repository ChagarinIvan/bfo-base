<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'
import Button from 'primevue/button'
import ProgressSpinner from 'primevue/progressspinner'
import Tag from 'primevue/tag'
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
import {
    rankCheckStatusLabel,
    rankCheckStatusSeverity,
} from './rankCheckModels'

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
let timer: ReturnType<typeof window.setInterval> | undefined

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

async function load(
    page = 1,
    perPage = pagination.value.perPage,
    silent = false,
): Promise<void> {
    if (!silent) {
        loading.value = true
    }
    error.value = ''
    try {
        const response = await getRankChecks(page, perPage)
        checks.value = response.data
        pagination.value = paginationFromHeaders(response.headers)
        if (!users.value.length) users.value = await getUsers()
        syncPolling()
    } catch {
        error.value = t('spa.rank_check.list_error')
    } finally {
        if (!silent) {
            loading.value = false
        }
    }
}

function stopPolling(): void {
    if (!timer) return

    window.clearInterval(timer)
    timer = undefined
}

function syncPolling(): void {
    const hasPendingChecks = checks.value.some(
        (check) => check.status !== 'READY' && check.status !== 'FAILED',
    )

    if (!hasPendingChecks) {
        stopPolling()
        return
    }

    if (timer) return

    timer = window.setInterval(() => {
        if (!loading.value) {
            void load(
                pagination.value.currentPage,
                pagination.value.perPage,
                true,
            )
        }
    }, 5000) as unknown as ReturnType<typeof window.setInterval>
}

async function onPage(event: PageState): Promise<void> {
    await load(event.page + 1, event.rows)
}

onMounted(() => void load())
onBeforeUnmount(() => stopPolling())
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
            <RouterLink
                class="rank-check-status-link"
                :to="`/app/rank-checks/${data.id}`"
            >
                <span class="rank-check-status-content">
                    <Tag
                        :value="rankCheckStatusLabel(data.status)"
                        :severity="rankCheckStatusSeverity(data.status)"
                    />
                    <ProgressSpinner
                        v-if="data.status === 'PARSING'"
                        class="rank-check-status-spinner"
                        stroke-width="6"
                        aria-label="Pending"
                    />
                </span>
            </RouterLink>
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
