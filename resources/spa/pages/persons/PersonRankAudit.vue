<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue'
import Button from 'primevue/button'
import Message from 'primevue/message'
import SlicePaginator from '../../components/SlicePaginator.vue'
import { getRankAudit, type RankAuditEntry } from '../../api/personRankAudit'
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
const dateFields = [
    'startedOn',
    'activatedOn',
    'finishedOn',
    'nextActivationOn',
] as const

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
                    <dl class="rank-audit-values">
                        <dt>{{ label('rankId') }}</dt>
                        <dd>{{ rankLabel(entry.applied.rankId) }}</dd>
                        <template v-for="key in dateFields" :key="key">
                            <dt>{{ label(key) }}</dt>
                            <dd>{{ value(entry.applied[key]) }}</dd>
                        </template>
                    </dl>
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
    display: grid;
    grid-template-columns: max-content 1fr;
    gap: 0.4rem 1rem;
}
.rank-audit-values dd {
    margin: 0;
}
</style>
