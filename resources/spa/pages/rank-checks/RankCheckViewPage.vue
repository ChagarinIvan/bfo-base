<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import Card from 'primevue/card'
import Message from 'primevue/message'
import ProgressSpinner from 'primevue/progressspinner'
import Tag from 'primevue/tag'
import { useRoute } from 'vue-router'
import {
    getRankCheck,
    listRankCheckRows,
    type RankCheck,
    type RankCheckRow,
} from '../../api/rankChecks'
import type { PaginationHeaders, User } from '../../api/types'
import { getUsers } from '../../api/users'
import ImpressionDetails from '../../components/ImpressionDetails.vue'
import ListingTable from '../../components/ListingTable.vue'
import { t } from '../../i18n'
import {
    debounce,
    hasTooShortNameSearch,
    resetPageOnFilterChange,
} from '../listingModels'
import {
    rankCheckStatusLabel,
    rankCheckStatusSeverity,
} from './rankCheckModels'
import RankCheckRowFilters from './RankCheckRowFilters.vue'

const route = useRoute()
const check = ref<RankCheck | null>(null)
const rows = ref<RankCheckRow[]>([])
const users = ref<User[]>([])
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 50,
    total: 0,
    lastPage: 1,
})
const loading = ref(true)
const error = ref('')
const name = ref('')
const group = ref('')
const hasPerson = ref<boolean | null>(null)
const isEqual = ref<boolean | null>(null)
let timer: ReturnType<typeof window.setInterval> | undefined
let latestRequest = 0

const columns = [
    { key: 'group', label: t('spa.rank_check.group'), defaultVisible: true },
    { key: 'name', label: t('spa.rank_check.name'), defaultVisible: true },
    { key: 'club', label: t('spa.rank_check.club'), defaultVisible: true },
    { key: 'rank', label: t('spa.rank_check.rank'), defaultVisible: true },
    { key: 'year', label: t('spa.rank_check.year'), defaultVisible: true },
    { key: 'equal', label: t('spa.rank_check.equal'), defaultVisible: true },
    {
        key: 'person',
        label: t('spa.rank_check.has_person'),
        defaultVisible: true,
    },
]

const debouncedNameSearch = debounce(() => {
    void load(resetPageOnFilterChange(pagination.value.currentPage))
})
const debouncedGroupSearch = debounce(() => {
    void load(resetPageOnFilterChange(pagination.value.currentPage))
})

function hasDifference(
    source: string | null,
    database: string | null,
    hasPerson: boolean,
): boolean {
    return hasPerson && source !== database
}

function valueClass(
    source: string | null,
    database: string | null,
    hasPerson: boolean,
): string {
    if (!hasPerson) return ''

    return source === database
        ? 'rank-check-value--match'
        : 'rank-check-value--mismatch'
}

function stopPolling(): void {
    if (!timer) return

    window.clearInterval(timer)
    timer = undefined
}

function startPolling(): void {
    if (timer) return

    timer = window.setInterval(
        () => void load(),
        5000,
    ) as unknown as ReturnType<typeof window.setInterval>
}

function onNameChange(value: string): void {
    name.value = value
    if (!value.trim()) {
        debouncedNameSearch.cancel()
        void load(resetPageOnFilterChange(pagination.value.currentPage))
        return
    }
    if (hasTooShortNameSearch(value)) {
        debouncedNameSearch.cancel()
        return
    }
    debouncedNameSearch()
}

function onGroupChange(value: string): void {
    group.value = value
    if (!value.trim()) {
        debouncedGroupSearch.cancel()
        void load(resetPageOnFilterChange(pagination.value.currentPage))
        return
    }
    if (hasTooShortNameSearch(value)) {
        debouncedGroupSearch.cancel()
        return
    }
    debouncedGroupSearch()
}

function onFilterChange(): void {
    void load(resetPageOnFilterChange(pagination.value.currentPage))
}

async function load(
    nextPage = pagination.value.currentPage,
    perPage = pagination.value.perPage,
): Promise<void> {
    const requestId = ++latestRequest
    try {
        loading.value = true
        error.value = ''
        check.value = await getRankCheck(String(route.params.rankCheckId))
        if (!users.value.length) users.value = await getUsers()
        if (check.value.status === 'READY') {
            const result = await listRankCheckRows(
                String(route.params.rankCheckId),
                nextPage,
                perPage,
                {
                    ...(name.value.trim().length >= 3
                        ? { name: name.value.trim() }
                        : {}),
                    ...(group.value.trim().length >= 3
                        ? { group: group.value.trim() }
                        : {}),
                    ...(hasPerson.value === null
                        ? {}
                        : { hasPerson: hasPerson.value }),
                    ...(isEqual.value === null
                        ? {}
                        : { isEqual: isEqual.value }),
                },
            )
            if (requestId !== latestRequest) return
            rows.value = result.data
            pagination.value = result.pagination
        }
        if (check.value.status !== 'PARSING' && timer) {
            stopPolling()
        }
    } catch {
        error.value = t('spa.rank_check.retry')
    } finally {
        loading.value = false
    }
}

async function onPage(event: { page: number; rows: number }): Promise<void> {
    await load(event.page + 1, event.rows)
}

onMounted(() => {
    startPolling()
    void load()
})

watch(
    () => route.params.rankCheckId,
    () => {
        check.value = null
        rows.value = []
        pagination.value = {
            currentPage: 1,
            perPage: 50,
            total: 0,
            lastPage: 1,
        }
        startPolling()
        void load()
    },
)

onBeforeUnmount(() => {
    stopPolling()
    debouncedNameSearch.cancel()
    debouncedGroupSearch.cancel()
})
</script>

<template>
    <section>
        <Message v-if="loading && !check" severity="info" :closable="false">
            {{ t('spa.rank_check.loading') }}
        </Message>
        <template v-else-if="check">
            <Card class="rank-check-details-card">
                <template #title>
                    {{ t('spa.rank_check.title') }} #{{ check.id }}
                </template>
                <template #content>
                    <table class="group-details-info">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    {{ t('spa.rank_check.status') }}
                                </th>
                                <td>
                                    <Tag
                                        :value="
                                            rankCheckStatusLabel(check.status)
                                        "
                                        :severity="
                                            rankCheckStatusSeverity(
                                                check.status,
                                            )
                                        "
                                    />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    {{ t('spa.rank_check.created') }}
                                </th>
                                <td>
                                    <ImpressionDetails
                                        :impression="check.created"
                                        :users="users"
                                        :label="t('spa.rank_check.created')"
                                    />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    {{ t('spa.rank_check.updated') }}
                                </th>
                                <td>
                                    <ImpressionDetails
                                        :impression="check.updated"
                                        :users="users"
                                        :label="t('spa.rank_check.updated')"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </template>
            </Card>

            <Message v-if="error" severity="error" :closable="false">
                {{ error }}
            </Message>
            <div
                v-else-if="check.status === 'PARSING'"
                class="rank-check-pending"
                role="status"
            >
                <ProgressSpinner aria-label="Processing" />
                <p>{{ t('spa.rank_check.parsing') }}</p>
            </div>
            <Message
                v-else-if="check.status === 'FAILED'"
                severity="error"
                :closable="false"
            >
                {{ check.error ?? t('spa.rank_check.failed') }}
            </Message>
            <ListingTable
                v-else
                table-id="rank-check-rows"
                :columns="columns"
                authenticated
                :items="rows"
                :pagination="pagination"
                :loading="loading"
                :error="error"
                :loading-label="t('spa.rank_check.loading_rows')"
                :empty-label="t('spa.rank_check.empty_rows')"
                table-class="rank-check-rows-table"
                @page="onPage"
            >
                <template #filters>
                    <RankCheckRowFilters
                        v-model:name="name"
                        v-model:group="group"
                        v-model:has-person="hasPerson"
                        v-model:is-equal="isEqual"
                        @name-change="onNameChange"
                        @group-change="onGroupChange"
                        @filter-change="onFilterChange"
                    />
                </template>
                <template #cell-group="{ data }">{{
                    data.group || '—'
                }}</template>
                <template #cell-name="{ data }">
                    <span
                        :class="
                            valueClass(
                                data.name,
                                data.databaseName,
                                data.hasPerson,
                            )
                        "
                    >
                        <RouterLink
                            v-if="data.personId"
                            :to="`/app/persons/${data.personId}`"
                        >
                            {{ data.name || t('spa.rank_check.empty') }}
                        </RouterLink>
                        <template v-else>{{
                            data.name || t('spa.rank_check.empty')
                        }}</template>
                    </span>
                    <template
                        v-if="
                            hasDifference(
                                data.name,
                                data.databaseName,
                                data.hasPerson,
                            )
                        "
                    >
                        <span class="rank-check-value__arrow">→ (</span>
                        <strong class="rank-check-value__correct">{{
                            data.databaseName || t('spa.rank_check.empty')
                        }}</strong
                        >)
                    </template>
                </template>
                <template #cell-club="{ data }">
                    <span
                        :class="
                            valueClass(
                                data.club,
                                data.databaseClub,
                                data.hasPerson,
                            )
                        "
                        >{{ data.club || t('spa.rank_check.empty') }}</span
                    >
                    <template
                        v-if="
                            hasDifference(
                                data.club,
                                data.databaseClub,
                                data.hasPerson,
                            )
                        "
                    >
                        <span class="rank-check-value__arrow">→ (</span>
                        <strong class="rank-check-value__correct">{{
                            data.databaseClub || t('spa.rank_check.empty')
                        }}</strong
                        >)
                    </template>
                </template>
                <template #cell-rank="{ data }">
                    <span
                        :class="
                            valueClass(
                                data.rank,
                                data.databaseRank,
                                data.hasPerson,
                            )
                        "
                        >{{ data.rank || t('spa.rank_check.empty') }}</span
                    >
                    <template
                        v-if="
                            hasDifference(
                                data.rank,
                                data.databaseRank,
                                data.hasPerson,
                            )
                        "
                    >
                        <span class="rank-check-value__arrow">→ (</span>
                        <strong class="rank-check-value__correct">{{
                            data.databaseRank || t('spa.rank_check.empty')
                        }}</strong
                        >)
                    </template>
                </template>
                <template #cell-year="{ data }">
                    <span
                        :class="
                            valueClass(
                                data.year,
                                data.databaseYear,
                                data.hasPerson,
                            )
                        "
                        >{{ data.year || t('spa.rank_check.empty') }}</span
                    >
                    <template
                        v-if="
                            hasDifference(
                                data.year,
                                data.databaseYear,
                                data.hasPerson,
                            )
                        "
                    >
                        <span class="rank-check-value__arrow">→ (</span>
                        <strong class="rank-check-value__correct">{{
                            data.databaseYear || t('spa.rank_check.empty')
                        }}</strong
                        >)
                    </template>
                </template>
                <template #cell-equal="{ data }">
                    <span
                        v-tooltip.top="
                            data.isEqual
                                ? t('spa.rank_check.equal')
                                : t('spa.rank_check.different')
                        "
                        class="rank-check-match-icon"
                        :class="
                            data.isEqual
                                ? 'rank-check-match-icon--yes'
                                : 'rank-check-match-icon--no'
                        "
                        role="img"
                        :aria-label="
                            data.isEqual
                                ? t('spa.rank_check.equal')
                                : t('spa.rank_check.different')
                        "
                    >
                        <i
                            :class="
                                data.isEqual
                                    ? 'pi pi-check-circle'
                                    : 'pi pi-times-circle'
                            "
                            aria-hidden="true"
                        />
                    </span>
                </template>
                <template #cell-person="{ data }">
                    <span
                        v-tooltip.top="
                            data.personId
                                ? t('spa.rank_check.person')
                                : t('spa.rank_check.no_person')
                        "
                        class="rank-check-person-icon"
                        :class="
                            data.personId
                                ? 'rank-check-person-icon--yes'
                                : 'rank-check-person-icon--no'
                        "
                        role="img"
                        :aria-label="
                            data.personId
                                ? t('spa.rank_check.person')
                                : t('spa.rank_check.no_person')
                        "
                    >
                        <i
                            :class="
                                data.personId
                                    ? 'pi pi-check-circle'
                                    : 'pi pi-times-circle'
                            "
                            aria-hidden="true"
                        />
                    </span>
                </template>
            </ListingTable>
        </template>
    </section>
</template>
