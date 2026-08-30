<script setup lang="ts">
import { ref, watch } from 'vue'
import type { AxiosError } from 'axios'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import Message from 'primevue/message'
import { useRoute, useRouter } from 'vue-router'
import { getCompetition } from '../../api/competitions'
import { deleteCompetition } from '../../api/competitions'
import { getCompetitionEvents } from '../../api/events'
import { t } from '../../i18n'
import { formatDateRange } from './competitionModels'
import type { Competition, Event } from '../../api/types'
import { useAuthStore } from '../../stores/auth'
import CompetitionActionMenu from '../../components/actions/CompetitionActionMenu.vue'
import ConfirmDeleteDialog from '../../components/actions/ConfirmDeleteDialog.vue'

const route = useRoute()
const router = useRouter()
const competition = ref<Competition | null>(null)
const events = ref<Event[]>([])
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

async function load(id: string): Promise<void> {
    loading.value = true
    error.value = ''

    try {
        const [loadedCompetition, loadedEvents] = await Promise.all([
            getCompetition(id),
            getCompetitionEvents(id),
        ])
        competition.value = loadedCompetition
        events.value = loadedEvents
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
    <Button
        class="back-button"
        icon="pi pi-arrow-left"
        :label="t('spa.competition.details.back')"
        severity="secondary"
        text
        @click="router.push('/app/competitions')"
    />

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
                <p class="competition-details-dates">
                    {{ formatDateRange(competition.from, competition.to) }}
                </p>
                <p>{{ competition.description }}</p>
                <CompetitionActionMenu
                    v-if="auth.isAuthenticated"
                    :competition-id="competition.id"
                    @delete="deleteDialogVisible = true"
                />
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
        </DataTable>
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
