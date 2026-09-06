<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import Button from 'primevue/button'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import Dialog from 'primevue/dialog'
import Message from 'primevue/message'
import { useRoute } from 'vue-router'
import {
    activatePersonRank,
    getPersonRankHistories,
    updatePersonRankActivation,
} from '../../api/personRankHistory'
import { getEventsByIds } from '../../api/events'
import { getRanks, type RankOption } from '../../api/ranks'
import type { Event, PersonRankHistory } from '../../api/types'
import ActionButton from '../../components/actions/ActionButton.vue'
import { t } from '../../i18n'
import { useAuthStore } from '../../stores/auth'

interface RankHistoryGroup {
    rankId: number
    rank: string
    items: PersonRankHistory[]
    startedOn: string
}

const route = useRoute()
const auth = useAuthStore()
const history = ref<PersonRankHistory[]>([])
const events = ref<Record<string, Event>>({})
const ranks = ref<RankOption[]>([])
const loading = ref(true)
const error = ref('')
const selected = ref<PersonRankHistory | null>(null)
const editDate = ref('')
const dateError = ref('')
const saving = ref(false)
const expandedRankIds = ref<Set<number>>(new Set())
let latestRequest = 0

const groupedHistory = computed<RankHistoryGroup[]>(() => {
    const groups = new Map<number, RankHistoryGroup>()

    for (const item of history.value) {
        const group = groups.get(item.rankId)
        if (group) {
            group.items.push(item)
            if (item.startedOn < group.startedOn) {
                group.startedOn = item.startedOn
            }
            continue
        }

        groups.set(item.rankId, {
            rankId: item.rankId,
            rank: rankLabel(item.rankId),
            items: [item],
            startedOn: item.startedOn,
        })
    }

    return [...groups.values()]
})

function groupFinishedOn(group: RankHistoryGroup): string | null {
    if (group.items.some((item) => item.finishedOn === null)) return null

    const finishedDates = group.items
        .map((item) => item.finishedOn)
        .filter((finishedOn): finishedOn is string => finishedOn !== null)

    return finishedDates.reduce(
        (latest, finishedOn) => (finishedOn > latest ? finishedOn : latest),
        finishedDates[0]!,
    )
}

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

function isRankExpanded(rankId: number): boolean {
    return expandedRankIds.value.has(rankId)
}

function toggleRank(rankId: number): void {
    const next = new Set(expandedRankIds.value)
    if (next.has(rankId)) {
        next.delete(rankId)
    } else {
        next.add(rankId)
    }
    expandedRankIds.value = next
}

function eventUrl(item: PersonRankHistory): string {
    return `/events/d/${item.distanceId}#${item.protocolLineId}`
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
    </div>
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
    <div v-else class="rank-history-groups">
        <div class="rank-history-group-header" aria-hidden="true">
            <span />
            <span>{{ t('spa.person_rank.rank') }}</span>
            <span>{{ t('spa.person_rank.confirmations') }}</span>
            <span>{{ t('spa.person_rank.started') }}</span>
            <span>{{ t('spa.person_rank.finished') }}</span>
        </div>
        <section v-for="group in groupedHistory" :key="group.rankId">
            <button
                class="rank-history-group"
                type="button"
                :aria-expanded="isRankExpanded(group.rankId)"
                @click="toggleRank(group.rankId)"
            >
                <i
                    :class="
                        isRankExpanded(group.rankId)
                            ? 'pi pi-chevron-down'
                            : 'pi pi-chevron-right'
                    "
                    aria-hidden="true"
                />
                <span>{{ group.rank }}</span>
                <span class="rank-history-group-count">
                    {{ group.items.length }}
                </span>
                <span>{{ group.startedOn }}</span>
                <span>{{
                    groupFinishedOn(group) ?? t('spa.person_rank.current')
                }}</span>
            </button>
            <DataTable
                v-if="isRankExpanded(group.rankId)"
                :value="group.items"
                striped-rows
            >
                <Column :header="t('spa.person_rank.rank')">
                    <template #body="{ data }">{{
                        rankLabel(data.rankId)
                    }}</template>
                </Column>
                <Column
                    field="achievedOn"
                    :header="t('spa.person_rank.completed')"
                />
                <Column :header="t('spa.person_rank.activated')">
                    <template #body="{ data }">{{
                        display(data.activatedOn)
                    }}</template>
                </Column>
                <Column :header="t('spa.person_rank.finished')">
                    <template #body="{ data }">{{
                        display(data.finishedOn)
                    }}</template>
                </Column>
                <Column :header="t('spa.person_rank.competition')">
                    <template #body="{ data }">
                        <RouterLink
                            v-if="data.competitionId"
                            :to="`/app/competitions/${data.competitionId}`"
                            >{{ display(competitionName(data)) }}</RouterLink
                        >
                        <span v-else>{{ display(competitionName(data)) }}</span>
                    </template>
                </Column>
                <Column :header="t('spa.person_rank.event')">
                    <template #body="{ data }">
                        <a :href="eventUrl(data)">{{
                            display(eventName(data))
                        }}</a>
                    </template>
                </Column>
                <Column
                    v-if="auth.isAuthenticated"
                    :header="t('spa.person.actions')"
                >
                    <template #body="{ data }">
                        <ActionButton
                            :label="
                                data.activatedOn
                                    ? t('spa.person_rank.edit_activation')
                                    : t('spa.person_rank.activate')
                            "
                            icon="pi pi-calendar"
                            severity="info"
                            @click="openActivation(data)"
                        />
                    </template>
                </Column>
            </DataTable>
        </section>
    </div>

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
