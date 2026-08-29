<script setup lang="ts">
import { onMounted, ref } from 'vue'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Message from 'primevue/message'
import Paginator, { type PageState } from 'primevue/paginator'
import Select from 'primevue/select'
import Toolbar from 'primevue/toolbar'
import { useRouter } from 'vue-router'
import { api } from '../../api/client'
import { getYears } from '../../api/years'
import { useAuthStore } from '../../stores/auth'
import { t } from '../../i18n'
import {
    competitionQuery,
    formatDateRange,
    massIconClass,
    paginationFromHeaders,
    shouldLoadUsers,
} from './competitionModels'
import type {
    Competition,
    Impression,
    PaginationHeaders,
    User,
} from '../../api/types'

const competitions = ref<Competition[]>([])
const users = ref<User[]>([])
const years = ref<number[]>([])
const year = ref<number | null>(null)
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    total: 0,
    lastPage: 1,
})
const loading = ref(false)
const error = ref('')
const auth = useAuthStore()
const router = useRouter()

async function onYearChange(value: number | null): Promise<void> {
    if (value !== null) {
        year.value = value
        await load(1, pagination.value.perPage)
    }
}

async function onPage(event: PageState): Promise<void> {
    await load(event.page + 1, event.rows)
}

function userLabel(impression: Impression | undefined): string {
    if (!impression) return ''

    const user = users.value.find((item) => String(item.id) === impression.by)
    return (
        user?.name ||
        user?.email ||
        t('spa.competitions.unknown_user', { id: impression.by })
    )
}

async function load(
    page = 1,
    perPage = pagination.value.perPage,
): Promise<void> {
    if (year.value === null) return

    loading.value = true
    error.value = ''
    try {
        const response = await api.get<Competition[]>('/competitions', {
            params: competitionQuery(year.value, page, perPage),
        })
        competitions.value = response.data
        pagination.value = paginationFromHeaders(
            response.headers as Record<string, unknown>,
        )

        if (shouldLoadUsers(auth.isAuthenticated, users.value.length)) {
            users.value = (await api.get<User[]>('/users')).data
        }
    } catch {
        error.value = t('spa.competitions.error')
    } finally {
        loading.value = false
    }
}

async function initialize(): Promise<void> {
    try {
        years.value = await getYears()
        year.value =
            years.value.find((item) => item === new Date().getFullYear()) ??
            years.value[0] ??
            null
        await load()
    } catch {
        error.value = t('spa.competitions.error')
    }
}

onMounted(initialize)
</script>

<template>
    <Toolbar class="page-toolbar">
        <template #start>
            <div>
                <h1 class="page-title">{{ t('spa.competitions.title') }}</h1>
                <p class="page-subtitle">
                    {{ t('spa.competitions.subtitle') }}
                </p>
            </div>
        </template>
        <template #end>
            <Button
                v-if="auth.isAuthenticated"
                icon="pi pi-plus"
                :label="t('spa.nav.create')"
                severity="success"
                @click="router.push('/app/competitions/create')"
            />
        </template>
    </Toolbar>

    <Card class="filter-card">
        <template #content>
            <div class="filter-panel">
                <label for="competition-year">{{
                    t('spa.competitions.year')
                }}</label>
                <Select
                    id="competition-year"
                    v-model="year"
                    :options="years"
                    :placeholder="t('spa.competitions.year_placeholder')"
                    :disabled="loading"
                    @update:model-value="onYearChange"
                />
            </div>
        </template>
    </Card>

    <Message v-if="loading" severity="info" :closable="false">{{
        t('spa.competitions.loading')
    }}</Message>
    <Message v-else-if="error" severity="error" :closable="false">{{
        error
    }}</Message>
    <Message
        v-else-if="!competitions.length"
        severity="secondary"
        :closable="false"
        >{{ t('spa.competitions.empty') }}</Message
    >
    <DataTable
        v-else
        :value="competitions"
        striped-rows
        class="competitions-table"
    >
        <Column field="name" :header="t('spa.competition.create.name')" />
        <Column :header="t('spa.competitions.dates')">
            <template #body="{ data }">{{
                formatDateRange(data.from, data.to)
            }}</template>
        </Column>
        <Column
            field="description"
            :header="t('spa.competitions.description')"
        />
        <Column :header="t('spa.competitions.mass')">
            <template #body="{ data }">
                <i
                    :class="[
                        massIconClass(data.mass),
                        'mass-icon',
                        data.mass ? 'mass-icon--active' : 'mass-icon--inactive',
                    ]"
                    :aria-label="
                        t(
                            data.mass
                                ? 'spa.competitions.mass_yes'
                                : 'spa.competitions.mass_no',
                        )
                    "
                />
            </template>
        </Column>
        <Column
            v-if="auth.isAuthenticated"
            :header="t('spa.competitions.created')"
        >
            <template #body="{ data }">{{ userLabel(data.created) }}</template>
        </Column>
        <Column
            v-if="auth.isAuthenticated"
            :header="t('spa.competitions.updated')"
        >
            <template #body="{ data }">{{ userLabel(data.updated) }}</template>
        </Column>
    </DataTable>
    <Paginator
        v-if="pagination.total > 0"
        :first="(pagination.currentPage - 1) * pagination.perPage"
        :rows="pagination.perPage"
        :total-records="pagination.total"
        :rows-per-page-options="[10, 20, 50]"
        class="competitions-paginator"
        @page="onPage"
    />
</template>
