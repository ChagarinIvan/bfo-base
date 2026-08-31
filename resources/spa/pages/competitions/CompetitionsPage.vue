<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import Button from 'primevue/button'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import Paginator, { type PageState } from 'primevue/paginator'
import Select from 'primevue/select'
import Toolbar from 'primevue/toolbar'
import { useRouter } from 'vue-router'
import { api } from '../../api/client'
import { deleteCompetition } from '../../api/competitions'
import { getUsers } from '../../api/users'
import { getYears } from '../../api/years'
import { useAuthStore } from '../../stores/auth'
import { t } from '../../i18n'
import ImpressionDetails from '../../components/ImpressionDetails.vue'
import DateFilter from '../../components/DateFilter.vue'
import CompetitionActionMenu from '../../components/actions/CompetitionActionMenu.vue'
import ConfirmDeleteDialog from '../../components/actions/ConfirmDeleteDialog.vue'
import { useToast } from 'primevue/usetoast'
import {
    competitionQuery,
    debounce,
    formatDateRange,
    hasTooShortNameSearch,
    isApiValidationError,
    massIconClass,
    paginationFromHeaders,
    resetPageOnFilterChange,
    applyFieldErrors,
    yearSelectOptions,
} from './competitionModels'
import type { Competition, PaginationHeaders, User } from '../../api/types'

const competitions = ref<Competition[]>([])
const users = ref<User[]>([])
const years = ref<number[]>([])
const yearOptions = computed(() => yearSelectOptions(years.value))
const year = ref<number | null>(null)
const name = ref('')
const date = ref('')
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    total: 0,
    lastPage: 1,
})
const loading = ref(false)
const error = ref('')
const fieldErrors = reactive<Record<string, string>>({})
const deleting = ref(false)
const selectedCompetition = ref<Competition | null>(null)
const auth = useAuthStore()
const router = useRouter()
const toast = useToast()

const debouncedNameSearch = debounce(() => {
    void load(resetPageOnFilterChange(pagination.value.currentPage))
})

async function onYearChange(value: number | null): Promise<void> {
    if (value !== null) {
        year.value = value
        await load(
            resetPageOnFilterChange(pagination.value.currentPage),
            pagination.value.perPage,
        )
    }
}

function onNameChange(value: string | undefined): void {
    name.value = value ?? ''

    if (hasTooShortNameSearch(name.value)) {
        debouncedNameSearch.cancel()
        void load(resetPageOnFilterChange(pagination.value.currentPage))
        return
    }

    debouncedNameSearch()
}

async function onDateChange(value: string | undefined): Promise<void> {
    date.value = value ?? ''
    delete fieldErrors.date
    await load(
        resetPageOnFilterChange(pagination.value.currentPage),
        pagination.value.perPage,
    )
}

async function onPage(event: PageState): Promise<void> {
    await load(event.page + 1, event.rows)
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
            params: competitionQuery({
                year: year.value,
                name: name.value,
                date: date.value,
                page,
                perPage,
            }),
        })
        competitions.value = response.data
        pagination.value = paginationFromHeaders(
            response.headers as Record<string, unknown>,
        )

        if (auth.isAuthenticated) {
            users.value = await getUsers()
        }
    } catch (exception: unknown) {
        if (isApiValidationError(exception)) {
            applyFieldErrors(exception.response.data.errors, fieldErrors)
            return
        }

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

async function deleteSelectedCompetition(): Promise<void> {
    if (!selectedCompetition.value) return

    deleting.value = true
    try {
        await deleteCompetition(selectedCompetition.value.id)
        toast.add({
            severity: 'success',
            summary: t('spa.competition.delete.success'),
            life: 3000,
        })
        await load(pagination.value.currentPage, pagination.value.perPage)
    } catch {
        error.value = t('spa.competition.delete.error')
    } finally {
        deleting.value = false
        selectedCompetition.value = null
    }
}

onMounted(initialize)

onBeforeUnmount(() => {
    debouncedNameSearch.cancel()
})
</script>

<template>
    <Toolbar class="page-toolbar">
        <template #start>
            <div>
                <h1 class="page-title">{{ t('spa.competitions.title') }}</h1>
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
                <div class="filter-field">
                    <label for="competition-year">{{
                        t('spa.competitions.year')
                    }}</label>
                    <Select
                        id="competition-year"
                        v-model="year"
                        :options="yearOptions"
                        option-label="label"
                        option-value="value"
                        :placeholder="t('spa.competitions.year_placeholder')"
                        filter
                        filter-match-mode="contains"
                        :filter-placeholder="t('spa.competitions.year_filter')"
                        :disabled="loading"
                        @update:model-value="onYearChange"
                    />
                </div>
                <div class="filter-field">
                    <label for="competition-name-filter">{{
                        t('spa.competitions.name_filter')
                    }}</label>
                    <InputText
                        id="competition-name-filter"
                        v-model="name"
                        @update:model-value="onNameChange"
                    />
                    <small
                        v-if="hasTooShortNameSearch(name)"
                        class="filter-hint"
                        >{{ t('spa.competitions.name_hint') }}</small
                    >
                </div>
                <DateFilter
                    v-model="date"
                    input-id="competition-date-filter"
                    :label="t('spa.competitions.date_filter')"
                    :disabled="loading"
                    :error="fieldErrors.date"
                    @update:model-value="onDateChange"
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
        <Column field="name" :header="t('spa.competition.create.name')">
            <template #body="{ data }">
                <RouterLink :to="`/app/competitions/${data.id}`">
                    {{ data.name }}
                </RouterLink>
            </template>
        </Column>
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
                    role="img"
                />
            </template>
        </Column>
        <Column
            v-if="auth.isAuthenticated"
            :header="t('spa.competitions.created')"
        >
            <template #body="{ data }">
                <ImpressionDetails
                    :impression="data.created"
                    :users="users"
                    :label="t('spa.competitions.created')"
                />
            </template>
        </Column>
        <Column
            v-if="auth.isAuthenticated"
            :header="t('spa.competitions.updated')"
        >
            <template #body="{ data }">
                <ImpressionDetails
                    :impression="data.updated"
                    :users="users"
                    :label="t('spa.competitions.updated')"
                />
            </template>
        </Column>
        <Column
            v-if="auth.isAuthenticated"
            :header="t('spa.competition.actions')"
        >
            <template #body="{ data }">
                <CompetitionActionMenu
                    :competition-id="data.id"
                    @delete="selectedCompetition = data"
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
        class="competitions-paginator"
        @page="onPage"
    />
    <ConfirmDeleteDialog
        v-if="auth.isAuthenticated && selectedCompetition"
        :visible="Boolean(selectedCompetition)"
        :competition-name="selectedCompetition.name"
        :pending="deleting"
        @cancel="selectedCompetition = null"
        @confirm="deleteSelectedCompetition"
    />
</template>
