<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import Button from 'primevue/button'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import Paginator, { type PageState } from 'primevue/paginator'
import Select from 'primevue/select'
import Toolbar from 'primevue/toolbar'
import { getClubOptions } from '../../api/clubs'
import { getPersons } from '../../api/persons'
import { getRanks, type RankOption } from '../../api/ranks'
import { getUsers } from '../../api/users'
import type {
    ClubOption,
    PaginationHeaders,
    Person,
    User,
} from '../../api/types'
import PersonTable from '../../components/PersonTable.vue'
import { t } from '../../i18n'
import { useAuthStore } from '../../stores/auth'
import {
    applyFieldErrors,
    birthYearOptions,
    debounce,
    hasTooShortNameSearch,
    isApiValidationError,
    paginationFromHeaders,
    personQuery,
    resetPageOnFilterChange,
    shouldLoadUsers,
} from './personsModels'

const persons = ref<Person[]>([])
const users = ref<User[]>([])
const clubs = ref<ClubOption[]>([])
const ranks = ref<RankOption[]>([])
const name = ref('')
const clubId = ref('')
const rankId = ref<number | null>(null)
const birthYear = ref<number | null>(null)
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
let latestRequest = 0

const yearOptions = computed(() =>
    birthYearOptions().map((year) => ({ label: String(year), value: year })),
)
const rankLabels = computed(() =>
    Object.fromEntries(ranks.value.map((rank) => [rank.id, rank.label])),
)
const clubOptions = computed(() => [
    { id: '', name: t('spa.person.all_options') },
    ...clubs.value,
])

const debouncedNameSearch = debounce(() => {
    void load(resetPageOnFilterChange(pagination.value.currentPage))
})

function clearNameError(): void {
    delete fieldErrors.name
}

function onNameChange(value: string | undefined): void {
    name.value = value ?? ''
    clearNameError()
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

function onFilterChange(): void {
    void load(resetPageOnFilterChange(pagination.value.currentPage))
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
        const response = await getPersons(
            personQuery({
                name: name.value,
                clubId: clubId.value ? Number(clubId.value) : undefined,
                rankId: rankId.value ?? undefined,
                birthYear: birthYear.value ?? undefined,
                page,
                perPage,
            }),
        )
        if (requestId !== latestRequest) return
        persons.value = response.data
        pagination.value = paginationFromHeaders(response.headers)

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
        error.value = t('spa.person.error')
    } finally {
        if (requestId === latestRequest) loading.value = false
    }
}

async function initialize(): Promise<void> {
    try {
        const [loadedClubs, loadedRanks] = await Promise.all([
            getClubOptions(),
            getRanks(),
        ])
        clubs.value = loadedClubs
        ranks.value = loadedRanks
        await load()
    } catch {
        error.value = t('spa.person.error')
    }
}

onMounted(() => void initialize())
onBeforeUnmount(() => debouncedNameSearch.cancel())
</script>

<template>
    <Toolbar class="page-toolbar">
        <template #start>
            <h1 class="page-title">{{ t('spa.nav.persons') }}</h1>
        </template>
        <template #end>
            <a v-if="auth.isAuthenticated" href="/persons/create">
                <Button
                    :label="t('spa.person.create')"
                    icon="pi pi-plus"
                    severity="success"
                />
            </a>
        </template>
    </Toolbar>

    <Card class="filter-card">
        <template #content>
            <div class="filter-panel persons-filter-panel">
                <div class="filter-field">
                    <label for="person-name-filter">{{
                        t('spa.person.search')
                    }}</label>
                    <InputText
                        id="person-name-filter"
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
                    >
                        {{ t('spa.person.search_hint') }}
                    </small>
                </div>
                <div class="filter-field">
                    <label for="person-club-filter">{{
                        t('spa.person.club_filter')
                    }}</label>
                    <Select
                        id="person-club-filter"
                        v-model="clubId"
                        :options="clubOptions"
                        option-label="name"
                        option-value="id"
                        @update:model-value="onFilterChange"
                    />
                </div>
                <div class="filter-field">
                    <label for="person-rank-filter">{{
                        t('spa.person.rank_filter')
                    }}</label>
                    <Select
                        id="person-rank-filter"
                        v-model="rankId"
                        :options="[
                            { id: null, label: t('spa.person.all_options') },
                            ...ranks,
                        ]"
                        option-label="label"
                        option-value="id"
                        @update:model-value="onFilterChange"
                    />
                </div>
                <div class="filter-field">
                    <label for="person-birth-year-filter">{{
                        t('spa.person.birth_year_filter')
                    }}</label>
                    <Select
                        id="person-birth-year-filter"
                        v-model="birthYear"
                        :options="[
                            { label: t('spa.person.all_options'), value: null },
                            ...yearOptions,
                        ]"
                        option-label="label"
                        option-value="value"
                        filter
                        @update:model-value="onFilterChange"
                    />
                </div>
            </div>
        </template>
    </Card>

    <Message v-if="loading" severity="info" :closable="false">{{
        t('spa.person.loading')
    }}</Message>
    <Message v-else-if="error" severity="error" :closable="false">
        {{ error }}
        <Button :label="t('spa.person.retry')" text @click="void load()" />
    </Message>
    <Message
        v-else-if="!persons.length"
        severity="secondary"
        :closable="false"
        >{{ t('spa.person.empty') }}</Message
    >
    <PersonTable
        v-else
        :persons="persons"
        :users="users"
        :clubs="clubs"
        :authenticated="auth.isAuthenticated"
        :rank-labels="rankLabels"
    />
    <Paginator
        v-if="pagination.total > 0"
        :first="(pagination.currentPage - 1) * pagination.perPage"
        :rows="pagination.perPage"
        :total-records="pagination.total"
        :rows-per-page-options="[10, 20, 50]"
        class="persons-paginator"
        @page="onPage"
    />
</template>
