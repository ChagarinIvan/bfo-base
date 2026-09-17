<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import type { PageState } from 'primevue/paginator'
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
import FilterPanel from '../../components/FilterPanel.vue'
import YearFilter from '../../components/YearFilter.vue'
import CompetitionActionMenu from '../../components/actions/CompetitionActionMenu.vue'
import ConfirmDeleteDialog from '../../components/actions/ConfirmDeleteDialog.vue'
import ListingTable from '../../components/ListingTable.vue'
import MassCompetitionIndicator from '../../components/MassCompetitionIndicator.vue'
import { useToast } from 'primevue/usetoast'
import {
    competitionQuery,
    debounce,
    formatDateRange,
    hasTooShortNameSearch,
    isApiValidationError,
    paginationFromHeaders,
    resetPageOnFilterChange,
    applyFieldErrors,
} from './competitionModels'
import type { Competition, PaginationHeaders, User } from '../../api/types'

const competitions = ref<Competition[]>([])
const users = ref<User[]>([])
const years = ref<number[]>([])
const year = ref<number | null>(null)
const name = ref('')
const date = ref('')
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    hasNext: false,
})
const loading = ref(false)
const error = ref('')
const fieldErrors = reactive<Record<string, string>>({})
const deleting = ref(false)
const selectedCompetition = ref<Competition | null>(null)
const auth = useAuthStore()
const router = useRouter()
const toast = useToast()
const columns = computed(() => [
    {
        key: 'name',
        label: t('spa.competition.create.name'),
        defaultVisible: true,
    },
    { key: 'dates', label: t('spa.competitions.dates'), defaultVisible: true },
    {
        key: 'description',
        label: t('spa.competitions.description'),
        defaultVisible: true,
        field: 'description',
    },
    { key: 'mass', label: t('spa.competitions.mass'), defaultVisible: true },
    ...(auth.isAuthenticated
        ? [
              {
                  key: 'created',
                  label: t('spa.competitions.created'),
                  defaultVisible: true,
              },
              {
                  key: 'updated',
                  label: t('spa.competitions.updated'),
                  defaultVisible: true,
              },
              {
                  key: 'actions',
                  label: t('spa.competition.actions'),
                  defaultVisible: true,
              },
          ]
        : []),
])

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

    <ListingTable
        table-id="competitions"
        :columns="columns"
        :authenticated="auth.isAuthenticated"
        :items="competitions"
        :pagination="pagination"
        :loading="loading"
        :error="error"
        :loading-label="t('spa.competitions.loading')"
        :empty-label="t('spa.competitions.empty')"
        table-class="competitions-table"
        @page="onPage"
    >
        <template #filters>
            <FilterPanel>
                <YearFilter
                    v-model="year"
                    input-id="competition-year"
                    :years="years"
                    :disabled="loading"
                    @update:model-value="onYearChange"
                />
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
            </FilterPanel>
        </template>
        <template #cell-name="{ data }">
            <RouterLink :to="`/app/competitions/${data.id}`">
                {{ data.name }}
            </RouterLink>
        </template>
        <template #cell-dates="{ data }">{{
            formatDateRange(data.from, data.to)
        }}</template>
        <template #cell-mass="{ data }">
            <MassCompetitionIndicator :mass="data.mass" />
        </template>
        <template #cell-created="{ data }">
            <ImpressionDetails
                :impression="data.created"
                :users="users"
                :label="t('spa.competitions.created')"
            />
        </template>
        <template #cell-updated="{ data }">
            <ImpressionDetails
                :impression="data.updated"
                :users="users"
                :label="t('spa.competitions.updated')"
            />
        </template>
        <template #cell-actions="{ data }">
            <CompetitionActionMenu
                :competition-id="data.id"
                @delete="selectedCompetition = data"
            />
        </template>
    </ListingTable>
    <ConfirmDeleteDialog
        v-if="auth.isAuthenticated && selectedCompetition"
        :visible="Boolean(selectedCompetition)"
        :title="t('spa.competition.delete.title')"
        :confirmation="
            t('spa.competition.delete.confirm', {
                name: selectedCompetition.name,
            })
        "
        :cancel-label="t('spa.competition.delete.cancel')"
        :action-label="t('spa.competition.delete.action')"
        :pending="deleting"
        @cancel="selectedCompetition = null"
        @confirm="deleteSelectedCompetition"
    />
</template>
