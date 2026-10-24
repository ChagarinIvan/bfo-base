<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue'
import Button from 'primevue/button'
import Message from 'primevue/message'
import SlicePaginator from '../../components/SlicePaginator.vue'
import {
    getRankAudit,
    type AuditHistory,
    type RankAuditEntry,
} from '../../api/personRankAudit'
import type { PaginationHeaders } from '../../api/types'
import { t, type TranslationKey } from '../../i18n'

const props = defineProps<{
    personId: string
    refresh: number
    rankLabel: (id: number) => string
}>()
const rows = ref<RankAuditEntry[]>([])
const loading = ref(false)
const error = ref(false)
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    hasNext: false,
})
let request = 0

function label(key: string): string {
    return t(`spa.rank_audit.${key}` as TranslationKey)
}
function value(input: string | number | null | undefined): string {
    return input == null ? '—' : String(input)
}
function changes(entry: RankAuditEntry) {
    const before = new Map(
        (entry.before?.history ?? []).map((row) => [
            `${row.protocolLineId}:${row.rankId}`,
            row,
        ]),
    )
    const after = new Map(
        (entry.after?.history ?? []).map((row) => [
            `${row.protocolLineId}:${row.rankId}`,
            row,
        ]),
    )
    return [...new Set([...before.keys(), ...after.keys()])].flatMap((key) => {
        const old = before.get(key)
        const next = after.get(key)
        return JSON.stringify(old) === JSON.stringify(next)
            ? []
            : [{ key, before: old, after: next }]
    })
}
const historyFields: (keyof AuditHistory)[] = [
    'rankId',
    'achievedOn',
    'activatedOn',
    'startedOn',
    'finishedOn',
]
function fieldValue(
    row: AuditHistory | undefined,
    key: keyof AuditHistory,
): string {
    if (!row) return '—'
    return key === 'rankId' ? props.rankLabel(row.rankId) : value(row[key])
}

async function load(
    page = 1,
    perPage = pagination.value.perPage,
): Promise<void> {
    const current = ++request
    loading.value = true
    error.value = false
    rows.value = []
    try {
        const list = await getRankAudit(props.personId, page, perPage)
        if (current !== request) return
        rows.value = list.data
        pagination.value = list.pagination
    } catch {
        if (current === request) error.value = true
    } finally {
        if (current === request) loading.value = false
    }
}
watch(
    () => [props.personId, props.refresh],
    () => void load(),
    { immediate: true },
)
onBeforeUnmount(() => {
    request++
})
</script>

<template>
    <section class="rank-audit" aria-labelledby="rank-audit-title">
        <div class="page-toolbar">
            <h2 id="rank-audit-title">{{ label('title') }}</h2>
            <Button
                :label="label('refresh')"
                icon="pi pi-refresh"
                :disabled="loading"
                @click="void load()"
            />
        </div>
        <Message v-if="loading" severity="info" :closable="false">{{
            label('loading')
        }}</Message>
        <Message v-else-if="error" severity="error" :closable="false">
            {{ label('error') }}
            <Button :label="label('retry')" text @click="void load()" />
        </Message>
        <template v-else>
            <Message
                v-if="!rows.length"
                severity="secondary"
                :closable="false"
                >{{ label('empty') }}</Message
            >
            <template v-else>
                <p>{{ label('coverage') }}</p>
                <details
                    v-for="entry in rows"
                    :key="entry.id"
                    class="rank-audit-entry"
                >
                    <summary>
                        {{ entry.created.at }} · {{ label(entry.trigger) }}
                    </summary>
                    <p>
                        {{ label('actor') }}:
                        {{
                            entry.trigger === 'scheduled'
                                ? label('system')
                                : `${label('user')} #${entry.created.by}`
                        }}
                    </p>
                    <Message
                        v-if="entry.after === null"
                        severity="error"
                        :closable="false"
                        >{{ label('processing_failed') }}</Message
                    >
                    <table
                        v-if="entry.before || entry.after"
                        class="rank-audit-values"
                    >
                        <caption>
                            {{
                                label('current')
                            }}
                        </caption>
                        <thead>
                            <tr>
                                <th>{{ label('field') }}</th>
                                <th>{{ label('before') }}</th>
                                <th>{{ label('after') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>{{ label('rankId') }}</th>
                                <td>
                                    {{
                                        entry.before
                                            ? rankLabel(
                                                  entry.before.current.rankId,
                                              )
                                            : '—'
                                    }}
                                </td>
                                <td>
                                    {{
                                        entry.after
                                            ? rankLabel(
                                                  entry.after.current.rankId,
                                              )
                                            : '—'
                                    }}
                                </td>
                            </tr>
                            <tr
                                v-for="key in [
                                    'activatedOn',
                                    'startedOn',
                                    'finishedOn',
                                ] as const"
                                :key="key"
                            >
                                <th>{{ label(key) }}</th>
                                <td>{{ value(entry.before?.current[key]) }}</td>
                                <td>{{ value(entry.after?.current[key]) }}</td>
                            </tr>
                            <tr>
                                <th>{{ label('nextActivationOn') }}</th>
                                <td>
                                    {{ value(entry.before?.nextActivationOn) }}
                                </td>
                                <td>
                                    {{ value(entry.after?.nextActivationOn) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <template v-if="entry.after">
                        <h3>{{ label('history_changes') }}</h3>
                        <p v-if="!changes(entry).length">
                            {{ label('history_unchanged') }}
                        </p>
                        <p v-else>{{ label('saved_sources') }}</p>
                        <table
                            v-for="change in changes(entry)"
                            :key="change.key"
                            class="rank-audit-values"
                        >
                            <caption>
                                {{
                                    label('protocolLineId')
                                }}
                                #{{
                                    (change.after ?? change.before)
                                        ?.protocolLineId
                                }}
                                ·
                                {{
                                    label('event')
                                }}
                                #{{
                                    (change.after ?? change.before)?.eventId
                                }}
                                ·
                                {{
                                    label(
                                        change.before
                                            ? change.after
                                                ? 'modified'
                                                : 'removed'
                                            : 'added',
                                    )
                                }}
                            </caption>
                            <thead>
                                <tr>
                                    <th>{{ label('field') }}</th>
                                    <th>{{ label('before') }}</th>
                                    <th>{{ label('after') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="key in historyFields" :key="key">
                                    <th>{{ label(key) }}</th>
                                    <td>
                                        {{ fieldValue(change.before, key) }}
                                    </td>
                                    <td>{{ fieldValue(change.after, key) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </template>
                </details>
            </template>
            <SlicePaginator
                v-if="rows.length || pagination.currentPage > 1"
                :pagination="pagination"
                @page="void load($event.page + 1, $event.rows)"
            />
        </template>
    </section>
</template>

<style scoped>
.rank-audit {
    margin-top: 2rem;
}
.rank-audit-entry {
    border-top: 1px solid var(--p-content-border-color, #ddd);
    padding: 0.75rem 0;
}
.rank-audit-entry summary {
    cursor: pointer;
}
.rank-audit-values {
    width: 100%;
    margin: 1rem 0;
    border-collapse: collapse;
}
.rank-audit-values th,
.rank-audit-values td {
    text-align: left;
    padding: 0.4rem;
    border-bottom: 1px solid var(--p-content-border-color, #ddd);
}
</style>
