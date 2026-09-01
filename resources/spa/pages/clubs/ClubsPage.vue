<script setup lang="ts">
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import Paginator, { type PageState } from 'primevue/paginator'
import Toolbar from 'primevue/toolbar'
import Button from 'primevue/button'
import { useRouter } from 'vue-router'
import { getClubs } from '../../api/clubs'
import { getUsers } from '../../api/users'
import type { Club, PaginationHeaders, User } from '../../api/types'
import ImpressionDetails from '../../components/ImpressionDetails.vue'
import { t } from '../../i18n'
import { useAuthStore } from '../../stores/auth'
import {
    applyFieldErrors,
    clubQuery,
    debounce,
    hasTooShortNameSearch,
    isApiValidationError,
    paginationFromHeaders,
    resetPageOnFilterChange,
    shouldLoadUsers,
} from './clubModels'

const clubs = ref<Club[]>([])
const users = ref<User[]>([])
const name = ref('')
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    total: 0,
    lastPage: 1,
})
const loading = ref(false)
const error = ref('')
const fieldErrors = reactive<Record<string, string>>({})
const auth = useAuthStore()
const router = useRouter()
let latestRequest = 0

const debouncedNameSearch = debounce(() => {
    void load(resetPageOnFilterChange(pagination.value.currentPage))
})

function onNameChange(value: string | undefined): void {
    name.value = value ?? ''
    delete fieldErrors.name

    if (!name.value.trim()) {
        debouncedNameSearch.cancel()
        void load(resetPageOnFilterChange(pagination.value.currentPage))
        return
    }

    if (hasTooShortNameSearch(name.value)) {
        debouncedNameSearch.cancel()
        return
    }

    debouncedNameSearch()
}

async function onPage(event: PageState): Promise<void> {
    await load(event.page + 1, event.rows)
}

async function load(
    page = 1,
    perPage = pagination.value.perPage,
): Promise<void> {
    const requestId = ++latestRequest
    loading.value = true
    error.value = ''

    try {
        const response = await getClubs(
            clubQuery({ name: name.value, page, perPage }),
        )
        if (requestId !== latestRequest) return

        clubs.value = response.data
        pagination.value = paginationFromHeaders(
            response.headers as Record<string, unknown>,
        )

        if (shouldLoadUsers(auth.isAuthenticated, users.value.length)) {
            const loadedUsers = await getUsers()
            if (requestId === latestRequest) users.value = loadedUsers
        }
    } catch (exception: unknown) {
        if (requestId !== latestRequest) return

        if (isApiValidationError(exception)) {
            applyFieldErrors(exception.response.data.errors, fieldErrors)
            return
        }

        error.value = t('spa.clubs.error')
    } finally {
        if (requestId === latestRequest) loading.value = false
    }
}

onMounted(() => {
    void load()
})

onBeforeUnmount(() => {
    debouncedNameSearch.cancel()
})
</script>

<template>
    <Toolbar class="page-toolbar">
        <template #start>
            <h1 class="page-title">{{ t('spa.clubs.title') }}</h1>
        </template>
        <template #end>
            <Button
                v-if="auth.isAuthenticated"
                :label="t('spa.club.create.action')"
                icon="pi pi-plus"
                severity="success"
                @click="router.push('/app/clubs/create')"
            />
        </template>
    </Toolbar>

    <Card class="filter-card">
        <template #content>
            <div class="filter-panel">
                <div class="filter-field">
                    <label for="club-name-filter">{{
                        t('spa.clubs.name_filter')
                    }}</label>
                    <InputText
                        id="club-name-filter"
                        v-model="name"
                        :invalid="Boolean(fieldErrors.name)"
                        @update:model-value="onNameChange"
                    />
                    <small v-if="fieldErrors.name" class="p-error">{{
                        fieldErrors.name
                    }}</small>
                    <small
                        v-else-if="hasTooShortNameSearch(name)"
                        class="filter-hint"
                        >{{ t('spa.clubs.name_hint') }}</small
                    >
                </div>
            </div>
        </template>
    </Card>

    <Message v-if="loading" severity="info" :closable="false">{{
        t('spa.clubs.loading')
    }}</Message>
    <Message v-else-if="error" severity="error" :closable="false">{{
        error
    }}</Message>
    <Message v-else-if="!clubs.length" severity="secondary" :closable="false">{{
        t('spa.clubs.empty')
    }}</Message>
    <DataTable v-else :value="clubs" striped-rows class="clubs-table">
        <Column field="name" :header="t('spa.clubs.name')">
            <template #body="{ data }">
                <RouterLink :to="`/app/clubs/${data.id}`">
                    {{ data.name }}
                </RouterLink>
            </template>
        </Column>
        <Column field="personsCount" :header="t('spa.clubs.persons_count')" />
        <Column v-if="auth.isAuthenticated" :header="t('spa.clubs.created')">
            <template #body="{ data }">
                <ImpressionDetails
                    :impression="data.created"
                    :users="users"
                    :label="t('spa.clubs.created')"
                />
            </template>
        </Column>
        <Column v-if="auth.isAuthenticated" :header="t('spa.clubs.updated')">
            <template #body="{ data }">
                <ImpressionDetails
                    :impression="data.updated"
                    :users="users"
                    :label="t('spa.clubs.updated')"
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
        class="clubs-paginator"
        @page="onPage"
    />
</template>
