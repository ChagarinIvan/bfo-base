<script setup lang="ts">
import { ref, watch } from 'vue'
import type { AxiosError } from 'axios'
import Card from 'primevue/card'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import Message from 'primevue/message'
import Paginator, { type PageState } from 'primevue/paginator'
import { useRoute, useRouter } from 'vue-router'
import { getClub } from '../../api/clubs'
import { getPersons } from '../../api/persons'
import type { Club, PaginationHeaders, Person, User } from '../../api/types'
import Button from 'primevue/button'
import ImpressionDetails from '../../components/ImpressionDetails.vue'
import { t } from '../../i18n'
import { useAuthStore } from '../../stores/auth'
import { getUsers } from '../../api/users'
import { paginationFromHeaders } from '../listingModels'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const club = ref<Club | null>(null)
const persons = ref<Person[]>([])
const users = ref<User[]>([])
const personPagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    total: 0,
    lastPage: 1,
})
const loading = ref(true)
const error = ref('')

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
    const response = await getPersons({ clubId: Number(id), page, perPage })
    persons.value = response.data
    personPagination.value = paginationFromHeaders(response.headers)
}

async function load(id: string): Promise<void> {
    loading.value = true
    error.value = ''

    try {
        club.value = await getClub(id)
        await loadPersons(id)
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
    try {
        await loadPersons(String(route.params.id), event.page + 1, event.rows)
    } catch {
        error.value = t('spa.club.details.error')
    }
}

watch(
    () => String(route.params.id),
    (id) => void load(id),
    { immediate: true },
)
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
                <Button
                    v-if="auth.isAuthenticated"
                    icon="pi pi-pencil"
                    :label="t('spa.club.edit.action')"
                    text
                    @click="router.push(`/app/clubs/${club.id}/edit`)"
                />
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
            </template>
        </Card>

        <h2 class="section-title">{{ t('spa.club.details.persons') }}</h2>
        <Message v-if="!persons.length" severity="secondary" :closable="false">
            {{ t('spa.club.details.empty') }}
        </Message>
        <DataTable v-else :value="persons" striped-rows class="persons-table">
            <Column field="lastname" :header="t('spa.person.lastname')">
                <template #body="{ data }">
                    <a :href="`/persons/${data.id}/show`">{{
                        data.lastname
                    }}</a>
                </template>
            </Column>
            <Column field="firstname" :header="t('spa.person.firstname')" />
            <Column field="birthYear" :header="t('spa.person.birth_year')" />
            <Column
                v-if="auth.isAuthenticated"
                :header="t('spa.clubs.created')"
            >
                <template #body="{ data }">
                    <ImpressionDetails
                        :impression="data.created"
                        :users="users"
                        :label="t('spa.clubs.created')"
                    />
                </template>
            </Column>
            <Column
                v-if="auth.isAuthenticated"
                :header="t('spa.clubs.updated')"
            >
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
