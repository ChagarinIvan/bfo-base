<script setup lang="ts">
import { inject, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import Button from 'primevue/button'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import Paginator, { type PageState } from 'primevue/paginator'
import { useRoute, useRouter } from 'vue-router'
import { extractPerson, getPersonProtocolLines } from '../../api/protocolLines'
import { getYears } from '../../api/years'
import type { PaginationHeaders, ProtocolLine } from '../../api/types'
import DateFilter from '../../components/DateFilter.vue'
import FilterPanel from '../../components/FilterPanel.vue'
import YearFilter from '../../components/YearFilter.vue'
import ActionButton from '../../components/actions/ActionButton.vue'
import { useAuthStore } from '../../stores/auth'
import { t } from '../../i18n'
import { personContextKey } from './personContext'
import {
    applyFieldErrors,
    debounce,
    hasPersonMismatch,
    hasTooShortNameSearch,
    isApiValidationError,
    paginationFromHeaders,
    personProtocolLinesQuery,
    resetPageOnFilterChange,
} from './personViewModels'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const person = inject(personContextKey, ref(null))
const lines = ref<ProtocolLine[]>([])
const years = ref<number[]>([])
const year = ref<number | null>(null)
const competitionName = ref('')
const date = ref('')
const loading = ref(true)
const error = ref('')
const notFound = ref(false)
const fieldErrors = reactive<Record<string, string>>({})
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    total: 0,
    lastPage: 1,
})
let latestRequest = 0

const debouncedCompetitionSearch = debounce(() => {
    void load(resetPageOnFilterChange(pagination.value.currentPage))
})

function personId(): string {
    return String(route.params.personId)
}

async function load(
    page = 1,
    perPage = pagination.value.perPage,
): Promise<void> {
    const requestId = ++latestRequest
    loading.value = true
    error.value = ''
    notFound.value = false
    Object.keys(fieldErrors).forEach((field) => delete fieldErrors[field])

    try {
        const response = await getPersonProtocolLines(
            personProtocolLinesQuery({
                personId: personId(),
                withEvent: 1,
                withCompetition: 1,
                year: year.value ?? undefined,
                competitionName: competitionName.value,
                date: date.value,
                page,
                perPage,
            }),
        )
        if (requestId !== latestRequest) return
        lines.value = response.data
        pagination.value = paginationFromHeaders(response.headers)
    } catch (exception: unknown) {
        if (requestId !== latestRequest) return
        if (isApiValidationError(exception)) {
            applyFieldErrors(exception.response.data.errors, fieldErrors)
            return
        }
        const status = (exception as { response?: { status?: number } })
            .response?.status
        notFound.value = status === 404
        error.value = notFound.value
            ? t('spa.person_view.not_found')
            : t('spa.person_view.error')
    } finally {
        if (requestId === latestRequest) loading.value = false
    }
}

async function initialize(): Promise<void> {
    try {
        years.value = await getYears()
        await load()
    } catch {
        error.value = t('spa.person_view.error')
        loading.value = false
    }
}

async function onYearChange(value: number | null): Promise<void> {
    year.value = value
    await load(resetPageOnFilterChange(pagination.value.currentPage))
}

function onCompetitionNameChange(value: string | undefined): void {
    competitionName.value = value ?? ''
    delete fieldErrors.competitionName

    if (hasTooShortNameSearch(competitionName.value)) {
        debouncedCompetitionSearch.cancel()
        return
    }

    debouncedCompetitionSearch()
}

async function onDateChange(value: string | undefined): Promise<void> {
    date.value = value ?? ''
    await load(resetPageOnFilterChange(pagination.value.currentPage))
}

function onPage(event: PageState): void {
    void load(event.page + 1, event.rows)
}

function eventUrl(line: ProtocolLine): string {
    return (
        '/app/events/' +
        line.eventId +
        '?distanceId=' +
        line.distanceId +
        '#' +
        line.id
    )
}

function display(value: string | null): string {
    return value || '—'
}

function rowClass(line: ProtocolLine): string | undefined {
    return auth.isAuthenticated && hasPersonMismatch(line, person.value)
        ? 'person-view-mismatch-row'
        : undefined
}

async function extract(line: ProtocolLine): Promise<void> {
    const extracted = await extractPerson(line.id)
    await router.push(`/app/persons/${extracted.id}`)
}

let initialized = false

watch(
    () => personId(),
    () => {
        if (initialized) void load()
    },
)

onMounted(async () => {
    await initialize()
    initialized = true
})
onBeforeUnmount(() => debouncedCompetitionSearch.cancel())
</script>

<template>
    <h2 class="section-title">{{ t('spa.person_view.participation') }}</h2>
    <FilterPanel>
        <YearFilter
            v-model="year"
            class="col-3"
            input-id="person-view-year-filter"
            :years="years"
            :disabled="loading"
            @update:model-value="onYearChange"
        />
        <div class="filter-field">
            <label for="person-view-competition-name-filter">{{
                t('spa.competitions.name_filter')
            }}</label>
            <InputText
                id="person-view-competition-name-filter"
                v-model="competitionName"
                @update:model-value="onCompetitionNameChange"
            />
            <small v-if="fieldErrors.competitionName" class="field-error">{{
                fieldErrors.competitionName
            }}</small>
            <small
                v-if="hasTooShortNameSearch(competitionName)"
                class="filter-hint"
                >{{ t('spa.competitions.name_hint') }}</small
            >
        </div>
        <DateFilter
            v-model="date"
            input-id="person-view-date-filter"
            :label="t('spa.competitions.date_filter')"
            :disabled="loading"
            :error="fieldErrors.date"
            @update:model-value="onDateChange"
        />
    </FilterPanel>

    <Message v-if="loading" severity="info" :closable="false">{{
        t('spa.person_view.loading')
    }}</Message>
    <Message v-else-if="error" severity="error" :closable="false">
        {{ error }}
        <Button
            v-if="notFound"
            :label="t('spa.person_view.back')"
            text
            @click="router.push('/app/persons')"
        />
        <Button
            v-else
            :label="t('spa.person_view.retry')"
            text
            @click="void load()"
        />
    </Message>
    <Message v-else-if="!lines.length" severity="secondary" :closable="false">{{
        t('spa.person_view.empty')
    }}</Message>
    <DataTable
        v-else
        :value="lines"
        :row-class="rowClass"
        striped-rows
        class="person-view-table"
    >
        <Column :header="t('spa.person_view.competition')">
            <template #body="{ data }">
                <RouterLink
                    v-if="data.competitionId"
                    :to="'/app/competitions/' + data.competitionId"
                    >{{ display(data.competitionName) }}</RouterLink
                >
                <span v-else>{{ display(data.competitionName) }}</span>
            </template>
        </Column>
        <Column :header="t('spa.person_view.event')">
            <template #body="{ data }">
                <a :href="eventUrl(data)">{{ display(data.eventName) }}</a>
            </template>
        </Column>
        <Column :header="t('spa.person_view.name')">
            <template #body="{ data }">{{
                data.lastname + ' ' + data.firstname
            }}</template>
        </Column>
        <Column :header="t('spa.person_view.date')" field="eventDate" />
        <Column :header="t('spa.person_view.group')">
            <template #body="{ data }">{{ display(data.groupName) }}</template>
        </Column>
        <Column :header="t('spa.person_view.birth_year')">
            <template #body="{ data }">{{ display(data.year) }}</template>
        </Column>
        <Column :header="t('spa.person_view.result')">
            <template #body="{ data }">{{ display(data.time) }}</template>
        </Column>
        <Column :header="t('spa.person_view.place')">
            <template #body="{ data }">{{ display(data.place) }}</template>
        </Column>
        <Column :header="t('spa.person_view.complete_rank')">
            <template #body="{ data }">{{
                display(data.completeRank)
            }}</template>
        </Column>
        <Column
            v-if="auth.isAuthenticated"
            :header="t('spa.person_view.actions')"
        >
            <template #body="{ data }">
                <ActionButton
                    v-if="hasPersonMismatch(data, person)"
                    icon="pi pi-user-plus"
                    :label="t('spa.person_view.extract_person')"
                    severity="warn"
                    @click="extract(data)"
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
