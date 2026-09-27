<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import Button from 'primevue/button'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import Dialog from 'primevue/dialog'
import Message from 'primevue/message'
import { useRoute, useRouter } from 'vue-router'
import {
    activatePersonRank,
    getPersonRankHistories,
    updatePersonRankActivation,
} from '../../api/personRankHistory'
import { getEventsByIds } from '../../api/events'
import { getCupEventContexts } from '../../api/cups'
import { getRanks, type RankOption } from '../../api/ranks'
import { rebuildPersonRanks } from '../../api/persons'
import type { CupEventContext, Event, PersonRankHistory } from '../../api/types'
import ActionButton from '../../components/actions/ActionButton.vue'
import ListingTable from '../../components/ListingTable.vue'
import CupEventBadges from '../../components/CupEventBadges.vue'
import { contextsByEventId } from '../../components/cupEventContextModels'
import { protocolLineEventUrl } from '../../components/tableModels'
import { t } from '../../i18n'
import { useAuthStore } from '../../stores/auth'

interface RankHistoryGroup {
    id: string
    rankId: number
    rank: string
    items: PersonRankHistory[]
    startedOn: string
    finishedOn: string | null
}

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const history = ref<PersonRankHistory[]>([])
const events = ref<Record<string, Event>>({})
const cupEventContexts = ref<Record<string, CupEventContext[]>>({})
const loadedCupEventIds = ref<Set<string>>(new Set())
const ranks = ref<RankOption[]>([])
const loading = ref(true)
const error = ref('')
const selected = ref<PersonRankHistory | null>(null)
const editDate = ref('')
const dateError = ref('')
const saving = ref(false)
const rebuilding = ref(false)
const rebuildError = ref('')
const expandedRankIds = ref<Set<string>>(new Set())
let latestRequest = 0
const columns = computed(() => [
    {
        key: 'completed',
        label: t('spa.person_rank.completed'),
        defaultVisible: true,
    },
    { key: 'rank', label: t('spa.person_rank.rank'), defaultVisible: true },
    {
        key: 'changeType',
        label: t('spa.person_rank.change_type'),
        defaultVisible: true,
    },
    {
        key: 'activated',
        label: t('spa.person_rank.activated'),
        defaultVisible: true,
    },
    {
        key: 'competition',
        label: t('spa.person_rank.competition'),
        defaultVisible: true,
    },
    { key: 'event', label: t('spa.person_rank.event'), defaultVisible: true },
    {
        key: 'cups',
        label: t('spa.cup_event.context.cups'),
        defaultVisible: true,
    },
    ...(auth.isAuthenticated
        ? [
              {
                  key: 'actions',
                  label: t('spa.person.actions'),
                  defaultVisible: true,
              },
          ]
        : []),
])

const activeHistory = computed(() =>
    history.value.filter((item) => item.activatedOn !== null),
)
const pendingHistory = computed(() =>
    [...history.value]
        .filter((item) => item.activatedOn === null)
        .sort(
            (left, right) =>
                right.achievedOn.localeCompare(left.achievedOn) ||
                Number(right.id) - Number(left.id),
        ),
)
const timeline = computed(() =>
    [...activeHistory.value].sort(
        (left, right) =>
            right.achievedOn.localeCompare(left.achievedOn) ||
            Number(right.id) - Number(left.id),
    ),
)

const groupedHistory = computed<RankHistoryGroup[]>(() => {
    const groups = new Map<string, RankHistoryGroup>()
    const periodIds = new Map<string, string>()

    for (const rankId of new Set(
        activeHistory.value.map((item) => item.rankId),
    )) {
        const rankHistory = activeHistory.value
            .filter((item) => item.rankId === rankId)
            .sort((left, right) =>
                left.startedOn.localeCompare(right.startedOn),
            )
        let period: RankHistoryGroup | null = null

        for (const item of rankHistory) {
            if (
                period === null ||
                (period.finishedOn !== null &&
                    item.startedOn > period.finishedOn)
            ) {
                period = {
                    id: `${rankId}:${item.startedOn}`,
                    rankId,
                    rank: rankLabel(rankId),
                    items: [],
                    startedOn: item.startedOn,
                    finishedOn: item.finishedOn,
                }
                groups.set(period.id, period)
            } else if (
                period.finishedOn === null ||
                (item.finishedOn !== null &&
                    item.finishedOn > period.finishedOn)
            ) {
                period.finishedOn = item.finishedOn
            }

            periodIds.set(item.id, period.id)
        }
    }

    for (const item of timeline.value) {
        const groupId = periodIds.get(item.id)
        if (groupId !== undefined) groups.get(groupId)?.items.push(item)
    }

    const periods = [...groups.values()].sort((left, right) =>
        right.startedOn.localeCompare(left.startedOn),
    )

    if (pendingHistory.value.length > 0) {
        periods.push({
            id: 'pending',
            rankId: 0,
            rank: t('spa.person_rank.pending'),
            items: pendingHistory.value,
            startedOn: '—',
            finishedOn: null,
        })
    }

    return periods
})

const dialogVisible = computed({
    get: () => selected.value !== null,
    set: (value: boolean) => {
        if (!value) closeActivation()
    },
})

function personId(): string {
    return String(route.params.personId)
}

function rankLabel(id: number): string {
    return ranks.value.find((rank) => rank.id === id)?.label ?? String(id)
}

function changeTypeLabel(changeType: string): string {
    switch (changeType) {
        case 'completion':
            return t('spa.person_rank.change_completion')
        case 'extension':
            return t('spa.person_rank.change_extension')
        case 'promotion':
            return t('spa.person_rank.change_promotion')
        case 'lower_qualification':
        case 'downgrade':
            return t('spa.person_rank.change_lower_qualification')
        default:
            return '—'
    }
}

function eventFor(item: PersonRankHistory): Event | undefined {
    return events.value[item.eventId]
}

function eventName(item: PersonRankHistory): string | null {
    return eventFor(item)?.name ?? null
}

function competitionName(item: PersonRankHistory): string | null {
    return eventFor(item)?.competitionName ?? null
}

async function loadHistory(): Promise<void> {
    const requestId = ++latestRequest
    loading.value = true
    error.value = ''

    try {
        const loadedHistory = await getPersonRankHistories(personId())
        const loadedEvents = await getEventsByIds([
            ...new Set(loadedHistory.map((item) => item.eventId)),
        ])
        if (requestId !== latestRequest) return
        history.value = loadedHistory
        cupEventContexts.value = {}
        loadedCupEventIds.value = new Set()
        events.value = Object.fromEntries(
            loadedEvents.map((event) => [event.id, event]),
        )
    } catch {
        if (requestId !== latestRequest) return
        error.value = t('spa.person_rank.error')
    } finally {
        if (requestId === latestRequest) loading.value = false
    }
}

async function initialize(): Promise<void> {
    try {
        ranks.value = await getRanks()
        await loadHistory()
    } catch {
        error.value = t('spa.person_rank.error')
        loading.value = false
    }
}

function isRankExpanded(groupId: string): boolean {
    return expandedRankIds.value.has(groupId)
}

function toggleRank(groupId: string): void {
    const next = new Set(expandedRankIds.value)
    if (next.has(groupId)) {
        next.delete(groupId)
    } else {
        next.add(groupId)
        const group = groupedHistory.value.find((item) => item.id === groupId)
        if (group) {
            void loadCupEventContexts(group.items.map((item) => item.eventId))
        }
    }
    expandedRankIds.value = next
}

async function loadCupEventContexts(eventIds: string[]): Promise<void> {
    const ids = [...new Set(eventIds)].filter(
        (id) => !loadedCupEventIds.value.has(id),
    )
    if (ids.length === 0) {
        return
    }

    try {
        const contexts = await getCupEventContexts(ids)
        cupEventContexts.value = {
            ...cupEventContexts.value,
            ...contextsByEventId(contexts),
        }
        loadedCupEventIds.value = new Set([...loadedCupEventIds.value, ...ids])
    } catch {
        // The rank timeline remains usable when cup context cannot be loaded.
    }
}

function eventUrl(item: PersonRankHistory): string {
    return protocolLineEventUrl(
        item.eventId,
        item.protocolLineId,
        item.distanceId,
    )
}

async function rebuild(): Promise<void> {
    rebuilding.value = true
    rebuildError.value = ''
    try {
        await rebuildPersonRanks(personId())
        await loadHistory()
        await router.replace({
            query: { ...route.query, refresh: String(Date.now()) },
        })
    } catch {
        rebuildError.value = t('spa.person_rank.rebuild_error')
    } finally {
        rebuilding.value = false
    }
}

function display(value: string | null): string {
    return value || '—'
}

function openActivation(item: PersonRankHistory): void {
    selected.value = item
    editDate.value = item.activatedOn ?? ''
    dateError.value = ''
}

function closeActivation(): void {
    if (saving.value) return
    selected.value = null
    dateError.value = ''
}

async function saveActivation(): Promise<void> {
    if (!selected.value) return
    if (!editDate.value && !selected.value.activatedOn) {
        dateError.value = t('spa.person_rank.date_required')
        return
    }

    saving.value = true
    dateError.value = ''
    try {
        const payload = { date: editDate.value || null }
        if (selected.value.activatedOn) {
            await updatePersonRankActivation(
                selected.value.protocolLineId,
                payload,
            )
        } else {
            await activatePersonRank(selected.value.protocolLineId, {
                date: editDate.value,
            })
        }
        selected.value = null
        await loadHistory()
    } catch {
        dateError.value = t('spa.person_rank.save_error')
    } finally {
        saving.value = false
    }
}

onMounted(() => void initialize())
watch(
    () => personId(),
    () => void loadHistory(),
)
onBeforeUnmount(() => {
    latestRequest++
})
</script>

<template>
    <div class="page-toolbar">
        <h1 class="page-title">{{ t('spa.person_rank.title') }}</h1>
        <Button
            v-if="auth.isAuthenticated"
            :label="t('spa.person_rank.rebuild')"
            icon="pi pi-refresh"
            severity="success"
            :loading="rebuilding"
            @click="void rebuild()"
        />
    </div>
    <Message v-if="rebuildError" severity="error" :closable="false">{{
        rebuildError
    }}</Message>
    <Message v-if="loading" severity="info" :closable="false">
        {{ t('spa.person_rank.loading') }}
    </Message>
    <Message v-else-if="error" severity="error" :closable="false">
        {{ error }}
        <Button
            :label="t('spa.person_rank.retry')"
            text
            @click="void loadHistory()"
        />
    </Message>
    <Message
        v-else-if="!groupedHistory.length"
        severity="secondary"
        :closable="false"
    >
        {{ t('spa.person_rank.empty') }}
    </Message>
    <ListingTable
        v-else
        table-id="person-rank-history"
        :columns="columns"
        :authenticated="auth.isAuthenticated"
    >
        <template #default="{ isVisible }">
            <div class="rank-history-groups">
                <div class="rank-history-group-header" aria-hidden="true">
                    <span />
                    <span>{{ t('spa.person_rank.rank') }}</span>
                    <span>{{ t('spa.person_rank.confirmations') }}</span>
                    <span>{{ t('spa.person_rank.started') }}</span>
                    <span>{{ t('spa.person_rank.finished') }}</span>
                </div>
                <section v-for="group in groupedHistory" :key="group.id">
                    <button
                        class="rank-history-group"
                        type="button"
                        :aria-expanded="isRankExpanded(group.id)"
                        @click="toggleRank(group.id)"
                    >
                        <i
                            :class="
                                isRankExpanded(group.id)
                                    ? 'pi pi-chevron-down'
                                    : 'pi pi-chevron-right'
                            "
                            aria-hidden="true"
                        />
                        <span>{{ group.rank }}</span>
                        <span class="rank-history-group-count">{{
                            group.items.length
                        }}</span>
                        <span>{{ group.startedOn }}</span>
                        <span>{{ display(group.finishedOn) }}</span>
                    </button>
                    <DataTable
                        v-if="isRankExpanded(group.id) && group.items.length"
                        :value="group.items"
                        striped-rows
                        class="rank-history-timeline"
                    >
                        <Column
                            v-if="isVisible('completed')"
                            field="achievedOn"
                            :header="t('spa.person_rank.completed')"
                        />
                        <Column
                            v-if="isVisible('rank')"
                            :header="t('spa.person_rank.rank')"
                        >
                            <template #body="{ data }">{{
                                rankLabel(data.rankId)
                            }}</template>
                        </Column>
                        <Column
                            v-if="isVisible('changeType')"
                            :header="t('spa.person_rank.change_type')"
                        >
                            <template #body="{ data }">{{
                                changeTypeLabel(data.changeType)
                            }}</template>
                        </Column>
                        <Column
                            v-if="isVisible('activated')"
                            :header="t('spa.person_rank.activated')"
                        >
                            <template #body="{ data }">{{
                                display(data.activatedOn)
                            }}</template>
                        </Column>
                        <Column
                            v-if="isVisible('competition')"
                            :header="t('spa.person_rank.competition')"
                        >
                            <template #body="{ data }">
                                <RouterLink
                                    v-if="data.competitionId"
                                    :to="`/app/competitions/${data.competitionId}`"
                                    >{{
                                        display(competitionName(data))
                                    }}</RouterLink
                                >
                                <span v-else>{{
                                    display(competitionName(data))
                                }}</span>
                            </template>
                        </Column>
                        <Column
                            v-if="isVisible('event')"
                            :header="t('spa.person_rank.event')"
                        >
                            <template #body="{ data }">
                                <a :href="eventUrl(data)">{{
                                    display(eventName(data))
                                }}</a>
                            </template>
                        </Column>
                        <Column
                            v-if="isVisible('cups')"
                            :header="t('spa.cup_event.context.cups')"
                        >
                            <template #body="{ data }">
                                <CupEventBadges
                                    :contexts="
                                        cupEventContexts[data.eventId] ?? []
                                    "
                                />
                            </template>
                        </Column>
                        <Column
                            v-if="auth.isAuthenticated && isVisible('actions')"
                            :header="t('spa.person.actions')"
                        >
                            <template #body="{ data }">
                                <ActionButton
                                    :label="
                                        data.activatedOn
                                            ? t(
                                                  'spa.person_rank.edit_activation',
                                              )
                                            : t('spa.person_rank.activate')
                                    "
                                    :icon="
                                        data.activatedOn
                                            ? 'pi pi-pencil'
                                            : 'pi pi-check'
                                    "
                                    :severity="
                                        data.activatedOn ? 'info' : 'success'
                                    "
                                    @click="openActivation(data)"
                                />
                            </template>
                        </Column>
                    </DataTable>
                </section>
            </div>
        </template>
    </ListingTable>

    <Dialog
        v-model:visible="dialogVisible"
        modal
        :header="
            selected?.activatedOn
                ? t('spa.person_rank.edit_activation')
                : t('spa.person_rank.activate')
        "
    >
        <div class="form-field">
            <label for="person-rank-activation-date">{{
                t('spa.person_rank.activated')
            }}</label>
            <input
                id="person-rank-activation-date"
                v-model="editDate"
                class="form-control"
                type="date"
            />
            <small v-if="dateError" class="field-error">{{ dateError }}</small>
        </div>
        <div class="details-actions">
            <Button
                :label="t('spa.person_rank.cancel')"
                severity="secondary"
                text
                :disabled="saving"
                @click="closeActivation"
            />
            <Button
                :label="t('spa.person_rank.save')"
                severity="success"
                :loading="saving"
                @click="void saveActivation()"
            />
        </div>
    </Dialog>
</template>
