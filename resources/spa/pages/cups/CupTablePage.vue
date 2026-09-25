<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { isCancel } from 'axios'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import { useRoute, useRouter } from 'vue-router'
import { getCup, getCupEvents, getCupTable } from '../../api/cups'
import { getEventsByIds } from '../../api/events'
import type {
    Cup,
    CupTable,
    CupTableStage,
    PaginationHeaders,
} from '../../api/types'
import ListingTable from '../../components/ListingTable.vue'
import ActionButton from '../../components/actions/ActionButton.vue'
import ConfirmDeleteDialog from '../../components/actions/ConfirmDeleteDialog.vue'
import { useAuthStore } from '../../stores/auth'
import CupTypeIcon from '../../components/CupTypeIcon.vue'
import { protocolLineEventUrl } from '../../components/tableModels'
import FilterPanel from '../../components/FilterPanel.vue'
import { debounce, hasTooShortNameSearch } from '../listingModels'
import { t } from '../../i18n'
import { paginationFromHeaders } from '../listingModels'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const cup = ref<Cup | null>(null)
const table = ref<CupTable | null>(null)
const stages = ref<CupTableStage[]>([])
const stagesLoaded = ref(false)
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 50,
    hasNext: false,
})
const groupId = ref(String(route.params.groupId ?? ''))
const name = ref('')
const loading = ref(true)
const error = ref('')
const deleteCupVisible = ref(false)
const controller = ref<AbortController | null>(null)
const requestId = ref(0)
const columns = computed(() => {
    const tableStages = stages.value
    return [
        { key: 'place', label: t('app.common.place'), defaultVisible: true },
        { key: 'personName', label: t('app.common.fio'), defaultVisible: true },
        {
            key: 'personYear',
            label: t('app.common.birthday_year'),
            defaultVisible: true,
        },
        { key: 'clubName', label: t('app.club.name'), defaultVisible: true },
        ...tableStages.map((stage) => ({
            key: `stage-${stage.stageId}`,
            label: `${stage.date} ${stage.name}`,
            defaultVisible: true,
            field: `stages.${stage.stageId}.points`,
            required: true,
            configurable: false,
        })),
        {
            key: 'totalPoints',
            label: t('app.common.points'),
            defaultVisible: true,
        },
        {
            key: 'averagePoints',
            label: t('app.common.average'),
            defaultVisible: true,
        },
    ]
})

async function load(): Promise<void> {
    const id = ++requestId.value
    controller.value?.abort()
    const current = new AbortController()
    controller.value = current
    loading.value = true
    error.value = ''
    try {
        if (!cup.value)
            cup.value = await getCup(String(route.params.cupId), current.signal)
        const query: { name?: string } = {}
        const searchedName = name.value.trim()
        if (searchedName.length >= 3) query.name = searchedName
        const [cupEventsResponse, response] = await Promise.all([
            stagesLoaded.value
                ? Promise.resolve(null)
                : getCupEvents(String(route.params.cupId), {
                      page: 1,
                      perPage: 1000,
                  }),
            getCupTable(
                String(route.params.cupId),
                groupId.value,
                {
                    ...query,
                    page: pagination.value.currentPage,
                    perPage: pagination.value.perPage,
                },
                current.signal,
            ),
        ])
        const eventDetails = cupEventsResponse
            ? await getEventsByIds(
                  cupEventsResponse.data.map((item) => item.eventId),
              )
            : []
        if (id === requestId.value) {
            table.value = response.data
            if (cupEventsResponse) {
                stages.value = cupEventsResponse.data
                    .flatMap((cupEvent) => {
                        const event = eventDetails.find(
                            (item) => item.id === cupEvent.eventId,
                        )
                        return event
                            ? [
                                  {
                                      stageId: Number(cupEvent.id),
                                      eventId: event.id,
                                      date: event.date,
                                      name: event.name,
                                  },
                              ]
                            : []
                    })
                    .sort((a, b) => a.date.localeCompare(b.date))
                stagesLoaded.value = true
            }
            pagination.value = paginationFromHeaders(response.headers)
        }
    } catch (exception) {
        if (id === requestId.value && !isCancel(exception))
            error.value = t('spa.cups.error')
    } finally {
        if (id === requestId.value) loading.value = false
    }
}
const debouncedSearch = debounce(() => void load())

function onName(value: string | undefined): void {
    name.value = value ?? ''
    if (hasTooShortNameSearch(name.value)) {
        debouncedSearch.cancel()
        return
    }
    pagination.value = { ...pagination.value, currentPage: 1 }
    debouncedSearch()
}
function onGroupChange(): void {
    pagination.value = { ...pagination.value, currentPage: 1 }
    void router.replace({ params: { ...route.params, groupId: groupId.value } })
}
function onPage(page: { page: number; rows: number }): void {
    pagination.value = {
        ...pagination.value,
        currentPage: page.page + 1,
        perPage: page.rows,
    }
    void load()
}
function deleteCup(): void {
    window.location.assign(`/cups/${cup.value?.id}/delete`)
}
watch(
    () => String(route.params.groupId),
    (value) => {
        groupId.value = value
        void load()
    },
    { immediate: true },
)
onBeforeUnmount(() => {
    controller.value?.abort()
    debouncedSearch.cancel()
})
</script>

<template>
    <template v-if="cup">
        <Card class="competition-details-card">
            <template #title>{{ cup.name }}</template>
            <template #content>
                <table class="competition-details-info">
                    <tbody>
                        <tr>
                            <th>{{ t('spa.cup.form.year') }}</th>
                            <td>{{ cup.year }}</td>
                        </tr>
                        <tr>
                            <th>{{ t('spa.cup.type') }}</th>
                            <td>
                                <CupTypeIcon :type="cup.type" />
                                {{ t(`app.cup.type.${cup.type}` as never) }}
                            </td>
                        </tr>
                        <tr>
                            <th>{{ t('spa.cups.events_count') }}</th>
                            <td>{{ cup.eventsCount }}</td>
                        </tr>
                        <tr>
                            <th>{{ t('spa.cups.groups') }}</th>
                            <td>
                                <router-link
                                    v-for="group in cup.groups"
                                    :key="group.id"
                                    :to="`/app/cups/${cup.id}/table/${group.id}`"
                                    class="mr-2"
                                    >{{ group.name }}</router-link
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>
            </template>
        </Card>
        <div v-if="auth.isAuthenticated" class="details-actions mb-3">
            <ActionButton
                as="a"
                :href="`/app/cups/${cup.id}/edit`"
                icon="pi pi-pencil"
                :label="t('spa.cups.edit.action')"
            />
            <ActionButton
                as="a"
                :href="`/app/cups/${cup.id}/events/create`"
                icon="pi pi-plus"
                :label="t('app.competition.add_event')"
                severity="success"
            />
            <ActionButton
                as="a"
                :href="`/cups/${cup.id}/cache`"
                icon="pi pi-refresh"
                :label="t('app.common.cache_clear')"
                severity="warn"
            />
            <ActionButton
                as="a"
                :href="`/cups/${cup.id}/export`"
                icon="pi pi-download"
                :label="t('app.cup.table.export')"
                severity="info"
            />
            <ActionButton
                as="a"
                :href="`/app/cups/${cup.id}/table/${groupId}`"
                icon="pi pi-table"
                :label="t('spa.cups.table')"
            />
            <ActionButton
                icon="pi pi-trash"
                :label="t('spa.cups.delete.action')"
                severity="danger"
                @click="deleteCupVisible = true"
            />
        </div>
        <div class="flex gap-2 mb-3">
            <router-link :to="`/app/cups/${cup.id}`">{{
                t('spa.cups.stages_tab')
            }}</router-link
            ><span>{{ t('spa.cups.table_tab') }}</span>
        </div>
        <FilterPanel>
            <div class="filter-field">
                <label>{{ t('spa.cups.table_group') }}</label
                ><Select
                    v-model="groupId"
                    :options="cup.groups"
                    option-label="name"
                    option-value="id"
                    @change="onGroupChange"
                />
            </div>
            <div class="filter-field">
                <label>{{ t('spa.cups.table_person_filter') }}</label
                ><InputText v-model="name" @update:model-value="onName" /><small
                    v-if="hasTooShortNameSearch(name)"
                    class="filter-hint"
                    >{{ t('spa.cups.table_person_filter_hint') }}</small
                >
            </div>
        </FilterPanel>
        <ActionButton
            v-if="auth.isAuthenticated"
            as="a"
            :href="`/cups/${cup.id}/${groupId}/table-export`"
            icon="pi pi-download"
            :label="t('app.cup.table.export')"
            severity="info"
            class="mb-3"
        />
        <ListingTable
            table-id="cup-table-v2"
            :columns="columns"
            :items="table ?? []"
            :pagination="pagination"
            :loading="loading"
            :error="error"
            :loading-label="t('spa.cups.loading')"
            :empty-label="t('spa.cups.table_empty')"
            :rows-per-page-options="[20, 50, 100]"
            @page="onPage"
        >
            <template #cell-personName="{ data }">
                <a :href="`/app/persons/${data.personId}`">{{
                    data.personName
                }}</a>
            </template>
            <template
                v-for="stage in stages"
                #[`cell-stage-${stage.stageId}`]="{ data }"
                :key="stage.stageId"
            >
                <template v-if="data.stages[String(stage.stageId)]">
                    <a
                        :class="{
                            'font-bold text-info':
                                data.stages[String(stage.stageId)].counted,
                            'text-body':
                                !data.stages[String(stage.stageId)].counted,
                        }"
                        :href="
                            protocolLineEventUrl(
                                stage.eventId,
                                data.stages[String(stage.stageId)]
                                    .protocolLineId,
                                data.stages[String(stage.stageId)].distanceId,
                            )
                        "
                        >{{ data.stages[String(stage.stageId)].points }}</a
                    > </template
                ><template v-else>—</template>
            </template>
        </ListingTable>
        <ConfirmDeleteDialog
            v-if="auth.isAuthenticated"
            :visible="deleteCupVisible"
            :title="t('spa.cups.delete.title')"
            :confirmation="t('spa.cups.delete.confirm', { name: cup.name })"
            :cancel-label="t('spa.cups.delete.cancel')"
            :action-label="t('spa.cups.delete.action')"
            @cancel="deleteCupVisible = false"
            @confirm="deleteCup"
        />
    </template>
</template>
