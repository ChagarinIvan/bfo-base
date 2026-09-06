<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import Button from 'primevue/button'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import Message from 'primevue/message'
import Paginator, { type PageState } from 'primevue/paginator'
import { useRoute, useRouter } from 'vue-router'
import { getPersonPayments } from '../../api/personPayments'
import { getUsers } from '../../api/users'
import { getYears } from '../../api/years'
import type { PaginationHeaders, PersonPayment, User } from '../../api/types'
import FilterPanel from '../../components/FilterPanel.vue'
import ImpressionDetails from '../../components/ImpressionDetails.vue'
import PersonPromptPersonInfo from '../../components/PersonPromptPersonInfo.vue'
import YearFilter from '../../components/YearFilter.vue'
import { t } from '../../i18n'
import { useAuthStore } from '../../stores/auth'
import {
    paginationFromHeaders,
    resetPageOnFilterChange,
} from '../listingModels'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const payments = ref<PersonPayment[]>([])
const users = ref<User[]>([])
const years = ref<number[]>([])
const year = ref<number | null>(null)
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    total: 0,
    lastPage: 1,
})
const loading = ref(true)
const error = ref('')
const notFound = ref(false)
let latestRequest = 0
let initialized = false

async function load(
    page = 1,
    perPage = pagination.value.perPage,
): Promise<void> {
    const requestId = ++latestRequest
    loading.value = true
    error.value = ''
    notFound.value = false

    try {
        const result = await getPersonPayments({
            personId: String(route.params.personId),
            year: year.value ?? undefined,
            page,
            perPage,
        })
        if (requestId !== latestRequest) return
        payments.value = result.data
        pagination.value = paginationFromHeaders(result.headers)
    } catch (exception: unknown) {
        if (requestId !== latestRequest) return
        const status = (exception as { response?: { status?: number } })
            .response?.status
        notFound.value = status === 404
        error.value = notFound.value
            ? t('spa.person_payment.not_found')
            : t('spa.person_payment.error')
    } finally {
        if (requestId === latestRequest) loading.value = false
    }
}

async function initialize(): Promise<void> {
    try {
        const [loadedYears, loadedUsers] = await Promise.all([
            getYears(),
            getUsers(),
        ])
        years.value = loadedYears
        users.value = loadedUsers
        await load()
        initialized = true
    } catch {
        error.value = t('spa.person_payment.error')
        loading.value = false
    }
}

async function onYearChange(value: number | null): Promise<void> {
    year.value = value
    await load(resetPageOnFilterChange(pagination.value.currentPage))
}

function onPage(event: PageState): void {
    void load(event.page + 1, event.rows)
}

watch(
    () => String(route.params.personId),
    () => {
        if (initialized) void load()
    },
)

onMounted(() => void initialize())
</script>

<template>
    <PersonPromptPersonInfo :person-id="String(route.params.personId)" />
    <div class="page-toolbar">
        <h1 class="page-title">{{ t('spa.person_payment.title') }}</h1>
        <Button
            v-if="auth.isAuthenticated"
            :label="t('spa.person_payment.create')"
            icon="pi pi-plus"
            severity="success"
            @click="
                router.push(
                    '/app/persons/' +
                        route.params.personId +
                        '/payments/create',
                )
            "
        />
    </div>
    <FilterPanel>
        <YearFilter
            v-model="year"
            class="col-3"
            input-id="person-payment-year-filter"
            :years="years"
            :disabled="loading"
            @update:model-value="onYearChange"
        />
    </FilterPanel>
    <Message v-if="loading" severity="info" :closable="false">{{
        t('spa.person_payment.loading')
    }}</Message>
    <Message v-else-if="error" severity="error" :closable="false">
        {{ error }}
        <Button
            v-if="notFound"
            :label="t('spa.person_payment.back')"
            text
            @click="router.push('/app/persons')"
        />
        <Button
            v-else
            :label="t('spa.person_payment.retry')"
            text
            @click="void load()"
        />
    </Message>
    <Message
        v-else-if="!payments.length"
        severity="secondary"
        :closable="false"
        >{{ t('spa.person_payment.empty') }}</Message
    >
    <DataTable v-else :value="payments" striped-rows>
        <Column field="year" :header="t('spa.person_payment.year')" />
        <Column field="date" :header="t('spa.person_payment.date')" />
        <Column :header="t('spa.person_payment.created')">
            <template #body="{ data }">
                <ImpressionDetails
                    :impression="data.created"
                    :users="users"
                    :label="t('spa.person_payment.created')"
                />
            </template>
        </Column>
        <Column :header="t('spa.person_payment.updated')">
            <template #body="{ data }">
                <ImpressionDetails
                    :impression="data.updated"
                    :users="users"
                    :label="t('spa.person_payment.updated')"
                />
            </template>
        </Column>
    </DataTable>
    <Paginator
        v-if="pagination.total > 0"
        :first="(pagination.currentPage - 1) * pagination.perPage"
        :rows="pagination.perPage"
        :total-records="pagination.total"
        :rows-per-page-options="[10, 20, 50]"
        @page="onPage"
    />
</template>
