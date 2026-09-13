<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import type { AxiosError } from 'axios'
import Card from 'primevue/card'
import Message from 'primevue/message'
import type { PageState } from 'primevue/paginator'
import { useRoute, useRouter } from 'vue-router'
import { getCompetition } from '../../api/competitions'
import { deleteCompetition } from '../../api/competitions'
import { deleteEvent, getCompetitionEvents } from '../../api/events'
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
import ActionButton from '../../components/actions/ActionButton.vue'
import ListingTable from '../../components/ListingTable.vue'
import MassCompetitionIndicator from '../../components/MassCompetitionIndicator.vue'

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
const selectedEvent = ref<Event | null>(null)
const eventDeleting = ref(false)
const auth = useAuthStore()
const eventColumns = computed(() => [
    {
        key: 'name',
        label: t('spa.competition.create.name'),
        defaultVisible: true,
    },
    {
        key: 'date',
        label: t('spa.competition.details.date'),
        defaultVisible: true,
        field: 'date',
    },
    {
        key: 'description',
        label: t('spa.competitions.description'),
        defaultVisible: true,
        field: 'description',
    },
    {
        key: 'participants',
        label: t('spa.competition.details.participants'),
        defaultVisible: true,
        field: 'participantsCount',
    },
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
        if (isNotFound(exception)) {
            await router.replace({ name: 'not-found' })
            return
        }

        error.value = t('spa.competition.details.error')
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

async function deleteCurrentEvent(): Promise<void> {
    if (!selectedEvent.value) return

    eventDeleting.value = true
    try {
        await deleteEvent(selectedEvent.value.id)
        await loadEvents(
            String(route.params.id),
            eventPagination.value.currentPage,
            eventPagination.value.perPage,
        )
    } catch {
        error.value = t('spa.event.delete.error')
    } finally {
        eventDeleting.value = false
        selectedEvent.value = null
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
                <table
                    class="competition-details-info"
                    :class="{ 'details-info--with-actions': auth.isAuthenticated }"
                >
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
                                <MassCompetitionIndicator :mass="competition.mass" />
                            </td>
                        </tr>
                        <tr v-if="auth.isAuthenticated">
                            <th scope="row">
                                {{ t('spa.competitions.created') }}
                            </th>
                            <td>
                                <ImpressionDetails
                                    :impression="competition.created"
                                    :users="users"
                                    :label="t('spa.competitions.created')"
                                />
                            </td>
                        </tr>
                        <tr v-if="auth.isAuthenticated">
                            <th scope="row">
                                {{ t('spa.competitions.updated') }}
                            </th>
                            <td>
                                <ImpressionDetails
                                    :impression="competition.updated"
                                    :users="users"
                                    :label="t('spa.competitions.updated')"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <CompetitionActionMenu
                    v-if="auth.isAuthenticated"
                    :competition-id="competition.id"
                    layout="row"
                    @delete="deleteDialogVisible = true"
                >
                    <template #between>
                        <span class="competition-legacy-actions">
                            <ActionButton
                                as="a"
                                :href="`/app/competitions/${competition.id}/events/create`"
                                icon="pi pi-plus"
                                :label="t('app.competition.add_event')"
                                severity="success"
                                class="competition-legacy-action"
                            />
                            <ActionButton
                                as="a"
                                :href="`/app/competitions/${competition.id}/events/unite`"
                                icon="pi pi-clone"
                                :label="t('app.competition.sum')"
                                severity="info"
                                class="competition-legacy-action"
                            />
                        </span>
                    </template>
                </CompetitionActionMenu>
            </template>
        </Card>

        <h2 class="section-title">{{ t('spa.competition.details.events') }}</h2>
        <ListingTable
            table-id="competition-events"
            :columns="eventColumns"
            :authenticated="auth.isAuthenticated"
            :items="events"
            :pagination="eventPagination"
            :empty-label="t('spa.competition.details.empty')"
            table-class="events-table"
            :rows-per-page-options="[10, 20]"
            @page="onEventPage"
        >
            <template #cell-name="{ data }">
                <a :href="`/app/events/${data.id}`">{{ data.name }}</a>
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
                <ActionButton
                    as="a"
                    :href="`/app/events/${data.id}/edit`"
                    icon="pi pi-pencil"
                    :label="t('spa.competition.edit.action')"
                    severity="secondary"
                    text
                    class="competition-legacy-action"
                />
                <ActionButton
                    :label="t('spa.event.delete.action')"
                    icon="pi pi-trash"
                    severity="danger"
                    text
                    class="competition-legacy-action"
                    @click="selectedEvent = data"
                />
            </template>
        </ListingTable>
        <ConfirmDeleteDialog
            v-if="auth.isAuthenticated"
            :visible="deleteDialogVisible"
            :title="t('spa.competition.delete.title')"
            :confirmation="
                t('spa.competition.delete.confirm', {
                    name: competition.name,
                })
            "
            :cancel-label="t('spa.competition.delete.cancel')"
            :action-label="t('spa.competition.delete.action')"
            :pending="deleting"
            @cancel="deleteDialogVisible = false"
            @confirm="deleteCurrentCompetition"
        />
        <ConfirmDeleteDialog
            v-if="auth.isAuthenticated"
            :visible="selectedEvent !== null"
            :title="t('spa.event.delete.title')"
            :confirmation="
                t('spa.event.delete.confirm', {
                    name: selectedEvent?.name ?? '',
                })
            "
            :cancel-label="t('spa.event.delete.cancel')"
            :action-label="t('spa.event.delete.action')"
            :pending="eventDeleting"
            @cancel="selectedEvent = null"
            @confirm="deleteCurrentEvent"
        />
    </template>
</template>
