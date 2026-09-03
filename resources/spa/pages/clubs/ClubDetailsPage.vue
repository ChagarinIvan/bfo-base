<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import type { AxiosError } from 'axios'
import Card from 'primevue/card'
import Message from 'primevue/message'
import Paginator, { type PageState } from 'primevue/paginator'
import { useRoute } from 'vue-router'
import { getClub } from '../../api/clubs'
import { getPersons } from '../../api/persons'
import { getRanks, type RankOption } from '../../api/ranks'
import type { Club, PaginationHeaders, Person, User } from '../../api/types'
import ImpressionDetails from '../../components/ImpressionDetails.vue'
import PersonFilters from '../../components/PersonFilters.vue'
import PersonTable from '../../components/PersonTable.vue'
import EditActionButton from '../../components/actions/EditActionButton.vue'
import { t } from '../../i18n'
import { useAuthStore } from '../../stores/auth'
import { getUsers } from '../../api/users'
import { paginationFromHeaders } from '../listingModels'
import {
    applyFieldErrors,
    debounce,
    hasTooShortNameSearch,
    isApiValidationError,
    personQuery,
    resetPageOnFilterChange,
} from '../persons/personsModels'

const route = useRoute()
const auth = useAuthStore()
const club = ref<Club | null>(null)
const persons = ref<Person[]>([])
const users = ref<User[]>([])
const ranks = ref<RankOption[]>([])
const name = ref('')
const rankId = ref<number | null>(null)
const birthYear = ref<number | null>(null)
const personPagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    total: 0,
    lastPage: 1,
})
const loading = ref(true)
const error = ref('')
const fieldErrors = ref<Record<string, string>>({})
let latestPersonRequest = 0
const rankLabels = computed(() =>
    Object.fromEntries(ranks.value.map((rank) => [rank.id, rank.label])),
)

function isNotFound(exception: unknown): boolean {
    return (
        typeof exception === 'object' &&
        exception !== null &&
        'isAxiosError' in exception &&
        exception.isAxiosError === true &&
        (exception as AxiosError).response?.status === 404
    )
}

async function loadPersons(
    id: string,
    page = 1,
    perPage = personPagination.value.perPage,
): Promise<void> {
    const requestId = ++latestPersonRequest
    const response = await getPersons(
        personQuery({
            name: name.value,
            clubId: Number(id),
            rankId: rankId.value ?? undefined,
            birthYear: birthYear.value ?? undefined,
            page,
            perPage,
        }),
    )
    if (requestId !== latestPersonRequest) return
    persons.value = response.data
    personPagination.value = paginationFromHeaders(response.headers)
}

const debouncedNameSearch = debounce(() => {
    void reloadPersons(
        resetPageOnFilterChange(personPagination.value.currentPage),
    )
})

function clearNameError(): void {
    delete fieldErrors.value.name
}

function onNameChange(value: string | undefined): void {
    name.value = value ?? ''
    clearNameError()

    if (!name.value.trim()) {
        debouncedNameSearch.cancel()
        void reloadPersons(
            resetPageOnFilterChange(personPagination.value.currentPage),
        )
        return
    }

    if (hasTooShortNameSearch(name.value)) {
        debouncedNameSearch.cancel()
        return
    }

    debouncedNameSearch()
}

function onFilterChange(): void {
    void reloadPersons(
        resetPageOnFilterChange(personPagination.value.currentPage),
    )
}

async function reloadPersons(
    page = 1,
    perPage = personPagination.value.perPage,
): Promise<void> {
    error.value = ''

    try {
        await loadPersons(String(route.params.id), page, perPage)
    } catch (exception: unknown) {
        if (isApiValidationError(exception)) {
            applyFieldErrors(exception.response.data.errors, fieldErrors.value)
            return
        }

        error.value = t('spa.club.details.error')
    }
}

async function load(id: string): Promise<void> {
    loading.value = true
    error.value = ''

    try {
        club.value = await getClub(id)
        const [loadedRanks] = await Promise.all([getRanks(), loadPersons(id)])
        ranks.value = loadedRanks
        users.value = auth.isAuthenticated ? await getUsers() : []
    } catch (exception: unknown) {
        club.value = null
        persons.value = []
        error.value = isNotFound(exception)
            ? t('spa.club.details.not_found')
            : t('spa.club.details.error')
    } finally {
        loading.value = false
    }
}

async function onPersonPage(event: PageState): Promise<void> {
    await reloadPersons(event.page + 1, event.rows)
}

watch(
    () => String(route.params.id),
    (id) => void load(id),
    { immediate: true },
)

onBeforeUnmount(() => debouncedNameSearch.cancel())
</script>

<template>
    <Message v-if="loading" severity="info" :closable="false">
        {{ t('spa.clubs.loading') }}
    </Message>
    <Message v-else-if="error" severity="error" :closable="false">
        {{ error }}
    </Message>
    <template v-else-if="club">
        <Card class="club-details-card">
            <template #title>
                {{ club.name }}
            </template>
            <template #content>
                <table class="club-details-info">
                    <tbody>
                        <tr>
                            <th scope="row">
                                {{ t('spa.clubs.persons_count') }}
                            </th>
                            <td>{{ club.personsCount }}</td>
                        </tr>
                        <tr v-if="auth.isAuthenticated">
                            <th scope="row">{{ t('spa.clubs.created') }}</th>
                            <td>
                                <ImpressionDetails
                                    :impression="club.created"
                                    :users="users"
                                    :label="t('spa.clubs.created')"
                                />
                            </td>
                        </tr>
                        <tr v-if="auth.isAuthenticated">
                            <th scope="row">{{ t('spa.clubs.updated') }}</th>
                            <td>
                                <ImpressionDetails
                                    :impression="club.updated"
                                    :users="users"
                                    :label="t('spa.clubs.updated')"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <EditActionButton
                    v-if="auth.isAuthenticated"
                    :to="`/app/clubs/${club.id}/edit`"
                    :label="t('spa.club.edit.action')"
                />
            </template>
        </Card>

        <h2 class="section-title">{{ t('spa.club.details.persons') }}</h2>
        <PersonFilters
            v-model:name="name"
            v-model:rank-id="rankId"
            v-model:birth-year="birthYear"
            :ranks="ranks"
            :field-errors="fieldErrors"
            id-prefix="club-person"
            @name-change="onNameChange"
            @filter-change="onFilterChange"
        />
        <Message v-if="!persons.length" severity="secondary" :closable="false">
            {{ t('spa.club.details.empty') }}
        </Message>
        <PersonTable
            v-else
            :persons="persons"
            :users="users"
            :clubs="[{ id: club.id, name: club.name }]"
            :authenticated="auth.isAuthenticated"
            :rank-labels="rankLabels"
        />
        <Paginator
            v-if="personPagination.total > 0"
            :first="
                (personPagination.currentPage - 1) * personPagination.perPage
            "
            :rows="personPagination.perPage"
            :total-records="personPagination.total"
            :rows-per-page-options="[10, 20, 50]"
            class="clubs-paginator"
            @page="onPersonPage"
        />
    </template>
</template>
