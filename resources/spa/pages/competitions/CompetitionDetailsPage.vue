<script setup lang="ts">
import { ref, watch } from 'vue'
import type { AxiosError } from 'axios'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import Message from 'primevue/message'
import Paginator, { type PageState } from 'primevue/paginator'
import { useRoute, useRouter } from 'vue-router'
import { getCompetition } from '../../api/competitions'
import { deleteCompetition } from '../../api/competitions'
import { getCompetitionEvents } from '../../api/events'
import { getUsers } from '../../api/users'
import { t } from '../../i18n'
import { formatDateRange, paginationFromHeaders } from './competitionModels'
import type {
    Competition,
    Event,
    PaginationHeaders,
    User,
} from '../../api/types'
import { useAuthStore } from '../../stores/auth'
import CompetitionActionMenu from '../../components/actions/CompetitionActionMenu.vue'
import ConfirmDeleteDialog from '../../components/actions/ConfirmDeleteDialog.vue'
import ImpressionDetails from '../../components/ImpressionDetails.vue'

const route = useRoute()
const router = useRouter()
const competition = ref<Competition | null>(null)
const events = ref<Event[]>([])
const users = ref<User[]>([])
const eventPagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    total: 0,
    lastPage: 1,
})
const loading = ref(true)
const error = ref('')
const deleting = ref(false)
const deleteDialogVisible = ref(false)
const auth = useAuthStore()

function isNotFound(exception: unknown): boolean {
    return (
        typeof exception === 'object' &&
        exception !== null &&
        'isAxiosError' in exception &&
        exception.isAxiosError === true &&
        (exception as AxiosError).response?.status === 404
    )
}

async function loadEvents(
    id: string,
    page = 1,
    perPage = eventPagination.value.perPage,
): Promise<void> {
    const response = await getCompetitionEvents(id, page, perPage)
    events.value = response.data
    eventPagination.value = paginationFromHeaders(response.headers)
}

async function load(id: string): Promise<void> {
    loading.value = true
    error.value = ''

    try {
        const loadedCompetition = await getCompetition(id)
        competition.value = loadedCompetition
        await loadEvents(id)
        users.value = auth.isAuthenticated ? await getUsers() : []
    } catch (exception: unknown) {
        competition.value = null
        events.value = []
        error.value = isNotFound(exception)
            ? t('spa.competition.details.not_found')
            : t('spa.competition.details.error')
    } finally {
        loading.value = false
    }
}

async function onEventPage(event: PageState): Promise<void> {
    await loadEvents(String(route.params.id), event.page + 1, event.rows)
}

watch(
    () => String(route.params.id),
    (id) => void load(id),
    { immediate: true },
)

async function deleteCurrentCompetition(): Promise<void> {
    if (!competition.value) return

    deleting.value = true
    try {
        await deleteCompetition(competition.value.id)
        await router.push('/app/competitions')
    } catch {
        error.value = t('spa.competition.delete.error')
    } finally {
        deleting.value = false
        deleteDialogVisible.value = false
    }
}
</script>

<template>
    <Message v-if="loading" severity="info" :closable="false">
        {{ t('spa.competitions.loading') }}
    </Message>
    <Message v-else-if="error" severity="error" :closable="false">
        {{ error }}
    </Message>
    <template v-else-if="competition">
        <Card class="competition-details-card">
            <template #title>{{ competition.name }}</template>
            <template #content>
                <div
                    v-if="auth.isAuthenticated"
                    class="competition-impressions"
                >
                    <div>
                        <span class="competition-details-label">
                            {{ t('spa.competitions.created') }}
                        </span>
                        <ImpressionDetails
                            :impression="competition.created"
                            :users="users"
                            :label="t('spa.competitions.created')"
                        />
                    </div>
                    <div>
                        <span class="competition-details-label">
                            {{ t('spa.competitions.updated') }}
                        </span>
                        <ImpressionDetails
                            :impression="competition.updated"
                            :users="users"
                            :label="t('spa.competitions.updated')"
                        />
                    </div>
                </div>
                <table class="competition-details-info">
                    <tbody>
                        <tr>
                            <th scope="row">
                                {{ t('spa.competitions.dates') }}
                            </th>
                            <td>
                                {{
                                    formatDateRange(
                                        competition.from,
                                        competition.to,
                                    )
                                }}
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                {{ t('spa.competitions.description') }}
                            </th>
                            <td>{{ competition.description }}</td>
                        </tr>
                        <tr>
                            <th scope="row">
                                {{ t('spa.competition.create.mass') }}
                            </th>
                            <td>
                                {{
                                    t(
                                        competition.mass
                                            ? 'spa.competitions.mass_yes'
                                            : 'spa.competitions.mass_no',
                                    )
                                }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <CompetitionActionMenu
                    v-if="auth.isAuthenticated"
                    :competition-id="competition.id"
                    @delete="deleteDialogVisible = true"
                />
                <span
                    v-if="auth.isAuthenticated"
                    class="competition-legacy-actions"
                >
                    <Button
                        as="a"
                        :href="`/events/${competition.id}/create`"
                        icon="pi pi-plus"
                        :label="t('app.competition.add_event')"
                        severity="success"
                        text
                    />
                    <Button
                        as="a"
                        :href="`/events/${competition.id}/sum`"
                        icon="pi pi-clone"
                        :label="t('app.competition.sum')"
                        severity="info"
                        text
                    />
                </span>
            </template>
        </Card>

        <h2 class="section-title">{{ t('spa.competition.details.events') }}</h2>
        <Message v-if="!events.length" severity="secondary" :closable="false">
            {{ t('spa.competition.details.empty') }}
        </Message>
        <DataTable v-else :value="events" striped-rows class="events-table">
            <Column field="name" :header="t('spa.competition.create.name')">
                <template #body="{ data }">
                    <a :href="`/events/${data.id}`">{{ data.name }}</a>
                </template>
            </Column>
            <Column field="date" :header="t('spa.competition.details.date')" />
            <Column
                field="description"
                :header="t('spa.competitions.description')"
            />
            <Column
                field="participantsCount"
                :header="t('spa.competition.details.participants')"
            />
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
                :header="t('spa.competition.edit.action')"
            >
                <template #body="{ data }">
                    <Button
                        as="a"
                        :href="`/events/${data.id}/edit`"
                        icon="pi pi-pencil"
                        :label="t('spa.competition.edit.action')"
                        severity="secondary"
                        text
                    />
                </template>
            </Column>
        </DataTable>
        <Paginator
            v-if="eventPagination.total > 0"
            :first="(eventPagination.currentPage - 1) * eventPagination.perPage"
            :rows="eventPagination.perPage"
            :total-records="eventPagination.total"
            :rows-per-page-options="[10, 20]"
            class="competitions-paginator"
            @page="onEventPage"
        />
        <ConfirmDeleteDialog
            v-if="auth.isAuthenticated"
            :visible="deleteDialogVisible"
            :competition-name="competition.name"
            :pending="deleting"
            @cancel="deleteDialogVisible = false"
            @confirm="deleteCurrentCompetition"
        />
    </template>
</template>
