<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { isCancel } from 'axios'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import Select from 'primevue/select'
import { useRoute, useRouter } from 'vue-router'
import { getCup, getCupEvents, getCupTable } from '../../api/cups'
import { getEventsByIds } from '../../api/events'
import type { Cup, CupTable } from '../../api/types'
import ListingTable from '../../components/ListingTable.vue'
import ActionButton from '../../components/actions/ActionButton.vue'
import { useAuthStore } from '../../stores/auth'
import CupTypeIcon from '../../components/CupTypeIcon.vue'
import { protocolLineEventUrl } from '../../components/tableModels'
import FilterPanel from '../../components/FilterPanel.vue'
import { hasTooShortNameSearch } from '../listingModels'
import { t } from '../../i18n'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const cup = ref<Cup | null>(null)
const table = ref<CupTable | null>(null)
const groupId = ref(String(route.params.groupId ?? ''))
const name = ref('')
const loading = ref(true)
const error = ref('')
const controller = ref<AbortController | null>(null)
const requestId = ref(0)

const visibleRows = computed(() => {
    const needle = name.value.trim().toLocaleLowerCase()
    return (
        table.value?.rows.filter(
            (row) =>
                needle.length < 3 ||
                row.personName.toLocaleLowerCase().includes(needle),
        ) ?? []
    )
})
const columns = computed(() => {
    const stages = table.value?.stages ?? []
    return [
        { key: 'place', label: t('app.common.place'), defaultVisible: true },
        { key: 'personName', label: t('app.common.fio'), defaultVisible: true },
        {
            key: 'personYear',
            label: t('app.common.birthday_year'),
            defaultVisible: true,
        },
        { key: 'clubName', label: t('app.club.name'), defaultVisible: true },
        ...stages.map((stage) => ({
            key: `stage-${stage.stageId}`,
            label: `${stage.date} ${stage.name}`,
            defaultVisible: true,
            field: `stages.${stage.stageId}.points`,
            required: true,
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
        const cupEvents = await getCupEvents(String(route.params.cupId), {
            page: 1,
            perPage: 1000,
        })
        const stageEvents = await getEventsByIds(
            cupEvents.data.map((event) => event.eventId),
        )
        const stageById = Object.fromEntries(
            stageEvents.map((event) => [event.id, event]),
        )
        const response = await getCupTable(
            String(route.params.cupId),
            groupId.value,
            {},
            current.signal,
        )
        if (id === requestId.value) {
            table.value = {
                ...response.data,
                stages: cupEvents.data
                    .map((cupEvent) => {
                        const event = stageById[cupEvent.eventId]
                        return event
                            ? {
                                  stageId: Number(cupEvent.id),
                                  eventId: event.id,
                                  date: event.date,
                                  name: event.name,
                              }
                            : null
                    })
                    .filter(
                        (stage): stage is NonNullable<typeof stage> =>
                            stage !== null,
                    ),
            }
        }
    } catch (exception) {
        if (id === requestId.value && !isCancel(exception))
            error.value = t('spa.cups.error')
    } finally {
        if (id === requestId.value) loading.value = false
    }
}
function onName(value: string | undefined): void {
    name.value = value ?? ''
}
function onGroupChange(): void {
    void router.replace({ params: { ...route.params, groupId: groupId.value } })
}
watch(
    () => String(route.params.groupId),
    (value) => {
        groupId.value = value
        void load()
    },
    { immediate: true },
)
onBeforeUnmount(() => controller.value?.abort())
</script>

<template>
    <Message v-if="loading" severity="info" :closable="false">{{
        t('spa.cups.loading')
    }}</Message>
    <Message v-else-if="error" severity="error" :closable="false">{{
        error
    }}</Message>
    <template v-else-if="cup && table">
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
            table-id="cup-table"
            :columns="columns"
            :items="visibleRows"
            :empty-label="t('spa.cups.table_empty')"
        >
            <template #cell-personName="{ data }">
                <a :href="`/app/persons/${data.personId}`">{{
                    data.personName
                }}</a>
            </template>
            <template
                v-for="stage in table.stages"
                #[`cell-stage-${stage.stageId}`]="{ data }"
                :key="stage.stageId"
            >
                <template v-if="data.stages[String(stage.stageId)]">
                    <a
                        :class="{
                            'font-bold text-info':
                                data.stages[String(stage.stageId)].counted,
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
    </template>
</template>
