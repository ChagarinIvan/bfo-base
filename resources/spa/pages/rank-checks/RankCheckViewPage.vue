<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import {
    getRankCheck,
    listRankCheckRows,
    type RankCheck,
    type RankCheckRow,
} from '../../api/rankChecks'
import { t } from '../../i18n'

const route = useRoute()
const check = ref<RankCheck | null>(null)
const rows = ref<RankCheckRow[]>([])
const lastPage = ref(1)
const error = ref('')
const page = ref(1)
let timer: ReturnType<typeof window.setInterval> | undefined

async function load(nextPage = page.value): Promise<void> {
    try {
        error.value = ''
        page.value = nextPage
        check.value = await getRankCheck(String(route.params.rankCheckId))
        if (check.value.status === 'READY') {
            const result = await listRankCheckRows(
                String(route.params.rankCheckId),
                page.value,
            )
            rows.value = result.data
            lastPage.value = result.lastPage
        }
        if (check.value.status !== 'PARSING' && timer) {
            window.clearInterval(timer)
            timer = undefined
        }
    } catch {
        error.value = t('spa.rank_check.retry')
    }
}

onMounted(async () => {
    await load()
    if (check.value?.status === 'PARSING')
        timer = window.setInterval(
            () => void load(),
            5000,
        ) as unknown as ReturnType<typeof window.setInterval>
})
onBeforeUnmount(() => {
    if (timer) window.clearInterval(timer)
})
</script>

<template>
    <section>
        <h1>{{ t('spa.rank_check.title') }}</h1>
        <p v-if="check?.status === 'PARSING'" role="status">
            {{ t('spa.rank_check.parsing') }}
        </p>
        <p v-if="error" role="alert">{{ error }}</p>
        <p v-if="check?.status === 'FAILED'" role="alert">
            {{ check.error ?? t('spa.rank_check.failed') }}
        </p>
        <table v-if="check?.status === 'READY'">
            <thead>
                <tr>
                    <th>{{ t('spa.rank_check.group') }}</th>
                    <th>{{ t('spa.rank_check.name') }}</th>
                    <th>{{ t('spa.rank_check.club') }}</th>
                    <th>{{ t('spa.rank_check.rank') }}</th>
                    <th>{{ t('spa.rank_check.year') }}</th>
                    <th>{{ t('spa.rank_check.equal') }}</th>
                    <th>{{ t('spa.rank_check.has_person') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in rows" :key="row.position">
                    <td>{{ row.group }}</td>
                    <td>
                        {{ row.name
                        }}<template
                            v-if="
                                row.databaseName &&
                                row.databaseName !== row.name
                            "
                        >
                            → {{ row.databaseName }}</template
                        >
                    </td>
                    <td>
                        {{ row.club
                        }}<template
                            v-if="
                                row.databaseClub &&
                                row.databaseClub !== row.club
                            "
                        >
                            → {{ row.databaseClub }}</template
                        >
                    </td>
                    <td>
                        {{ row.rank
                        }}<template
                            v-if="
                                row.databaseRank &&
                                row.databaseRank !== row.rank
                            "
                        >
                            → {{ row.databaseRank }}</template
                        >
                    </td>
                    <td>
                        {{ row.year
                        }}<template
                            v-if="
                                row.databaseYear &&
                                row.databaseYear !== row.year
                            "
                        >
                            → {{ row.databaseYear }}</template
                        >
                    </td>
                    <td>{{ row.isEqual ? '✓' : '✗' }}</td>
                    <td>
                        {{
                            row.hasPerson ? '✓' : t('spa.rank_check.no_person')
                        }}
                    </td>
                </tr>
            </tbody>
        </table>
        <nav
            v-if="check?.status === 'READY' && lastPage > 1"
            aria-label="Pagination"
        >
            <button
                type="button"
                :disabled="page <= 1"
                @click="void load(page - 1)"
            >
                ‹
            </button>
            <span>{{ page }} / {{ lastPage }}</span>
            <button
                type="button"
                :disabled="page >= lastPage"
                @click="void load(page + 1)"
            >
                ›
            </button>
        </nav>
    </section>
</template>
