<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import type { PageState } from 'primevue/paginator'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Button from 'primevue/button'
import Toolbar from 'primevue/toolbar'
import { useRouter } from 'vue-router'
import { getCups } from '../../api/cups'
import { getYears } from '../../api/years'
import type { Cup, CupGroup, PaginationHeaders } from '../../api/types'
import FilterPanel from '../../components/FilterPanel.vue'
import ImpressionDetails from '../../components/ImpressionDetails.vue'
import ListingTable from '../../components/ListingTable.vue'
import YearFilter from '../../components/YearFilter.vue'
import ConfirmDeleteDialog from '../../components/actions/ConfirmDeleteDialog.vue'
import CupActionMenu from '../../components/actions/CupActionMenu.vue'
import { t } from '../../i18n'
import { useAuthStore } from '../../stores/auth'
import {
    cupQuery,
    debounce,
    hasTooShortNameSearch,
    paginationFromHeaders,
    resetPageOnFilterChange,
} from './cupModels'

const cups = ref<Cup[]>([])
const years = ref<number[]>([])
const year = ref<number | null>(null)
const name = ref('')
const visible = ref<'all' | '1' | '0'>('1')
const loading = ref(false)
const error = ref('')
const selectedCup = ref<Cup | null>(null)
const deleting = ref(false)
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    hasNext: false,
})
const auth = useAuthStore()
const router = useRouter()
let latestRequest = 0

const columns = computed(() => [
    { key: 'name', label: t('spa.cups.name'), defaultVisible: true },
    {
        key: 'eventsCount',
        label: t('spa.cups.events_count'),
        field: 'eventsCount',
        defaultVisible: true,
    },
    { key: 'groups', label: t('spa.cups.groups'), defaultVisible: true },
    ...(auth.isAuthenticated
        ? [
              {
                  key: 'visible',
                  label: t('spa.cups.visibility'),
                  defaultVisible: true,
              },
              {
                  key: 'created',
                  label: t('spa.cups.created'),
                  defaultVisible: true,
              },
              {
                  key: 'updated',
                  label: t('spa.cups.updated'),
                  defaultVisible: true,
              },
              {
                  key: 'actions',
                  label: t('spa.cups.actions'),
                  defaultVisible: true,
              },
          ]
        : []),
])

const visibilityOptions = computed(() => [
    { label: t('spa.cups.visible_all'), value: 'all' },
    { label: t('spa.cups.visible_yes'), value: '1' },
    { label: t('spa.cups.visible_no'), value: '0' },
])

const debouncedSearch = debounce(() => {
    void load(resetPageOnFilterChange(pagination.value.currentPage))
})

async function load(
    page = 1,
    perPage = pagination.value.perPage,
): Promise<void> {
    const requestId = ++latestRequest
    if (year.value === null) return

    loading.value = true
    error.value = ''
    try {
        const query = cupQuery({
            year: year.value,
            name: name.value,
            page,
            perPage,
        })
        if (auth.isAuthenticated && visible.value !== 'all') {
            query.visible = visible.value
        }
        if (!auth.isAuthenticated) {
            query.visible = '1'
        }
        const response = await getCups(query)
        if (requestId !== latestRequest) return
        cups.value = response.data
        pagination.value = paginationFromHeaders(response.headers)
    } catch {
        if (requestId === latestRequest) error.value = t('spa.cups.error')
    } finally {
        if (requestId === latestRequest) loading.value = false
    }
}

async function initialize(): Promise<void> {
    try {
        years.value = await getYears()
        year.value =
            years.value.find((item) => item === new Date().getFullYear()) ??
            years.value[0] ??
            null
        await load()
    } catch {
        error.value = t('spa.cups.error')
    }
}

function onNameChange(): void {
    if (!name.value.trim()) {
        debouncedSearch.cancel()
        void load(resetPageOnFilterChange(pagination.value.currentPage))
        return
    }
    debouncedSearch()
}

async function onYearChange(value: number | null): Promise<void> {
    year.value = value
    await load(resetPageOnFilterChange(pagination.value.currentPage))
}

async function onVisibilityChange(value: 'all' | '1' | '0'): Promise<void> {
    visible.value = value
    await load(resetPageOnFilterChange(pagination.value.currentPage))
}

function onPage(event: PageState): void {
    void load(event.page + 1, event.rows)
}

function cupTableUrl(cup: Cup): string {
    return `/cups/${cup.id}/${cup.groups[0]?.id ?? ''}/table`
}

const cupGroupColors = [
    'blue',
    'green',
    'orange',
    'purple',
    'pink',
    'cyan',
    'indigo',
    'teal',
] as const

function cupGroupBadgeClass(group: CupGroup): string {
    let hash = 0
    for (const character of group.id) {
        hash = (hash * 31 + character.charCodeAt(0)) >>> 0
    }

    return `cup-group-badge--${cupGroupColors[hash % cupGroupColors.length]}`
}

function deleteSelectedCup(): void {
    if (!selectedCup.value) return

    deleting.value = true
    window.location.assign(`/cups/${selectedCup.value.id}/delete`)
}

watch(
    () => auth.isAuthenticated,
    (authenticated) => {
        if (!authenticated) visible.value = '1'
        void load(1)
    },
)

onMounted(() => void initialize())
onBeforeUnmount(() => debouncedSearch.cancel())
</script>

<template>
    <Toolbar class="page-toolbar">
        <template #start
            ><h1 class="page-title">{{ t('spa.cups.title') }}</h1></template
        >
        <template #end>
            <Button
                v-if="auth.isAuthenticated"
                icon="pi pi-plus"
                :label="t('spa.cups.create')"
                severity="success"
                @click="router.push('/cups/create')"
            />
        </template>
    </Toolbar>
    <ListingTable
        table-id="cups"
        :columns="columns"
        :authenticated="auth.isAuthenticated"
        :items="cups"
        :pagination="pagination"
        :loading="loading"
        :error="error"
        :loading-label="t('spa.cups.loading')"
        :empty-label="t('spa.cups.empty')"
        table-class="cups-table"
        @page="onPage"
    >
        <template #filters>
            <FilterPanel>
                <YearFilter
                    v-model="year"
                    input-id="cup-year"
                    :years="years"
                    :disabled="loading"
                    @update:model-value="onYearChange"
                />
                <div class="filter-field">
                    <label for="cup-name-filter">{{
                        t('spa.cups.name_filter')
                    }}</label>
                    <InputText
                        id="cup-name-filter"
                        v-model="name"
                        @update:model-value="onNameChange"
                    />
                    <small
                        v-if="hasTooShortNameSearch(name)"
                        class="filter-hint"
                        >{{ t('spa.cups.name_hint') }}</small
                    >
                </div>
                <div v-if="auth.isAuthenticated" class="filter-field">
                    <label for="cup-visibility-filter">{{
                        t('spa.cups.visibility')
                    }}</label>
                    <Select
                        id="cup-visibility-filter"
                        v-model="visible"
                        :options="visibilityOptions"
                        option-label="label"
                        option-value="value"
                        @update:model-value="onVisibilityChange"
                    />
                </div>
            </FilterPanel>
        </template>
        <template #cell-name="{ data }">
            <a :href="`/cups/${data.id}/show`">{{ data.name }}</a>
        </template>
        <template #cell-groups="{ data }">
            <a
                v-for="group in data.groups"
                :key="group.id"
                :href="`/cups/${data.id}/${group.id}/table`"
                :class="['cup-group-badge', cupGroupBadgeClass(group)]"
            >
                {{ group.name }}
            </a>
        </template>
        <template #cell-visible="{ data }">
            <span class="mass-competition-indicator__icon">
                <i
                    :class="[
                        data.visible
                            ? 'pi pi-check-square'
                            : 'pi pi-times-circle',
                        'mass-icon',
                        data.visible
                            ? 'mass-icon--active'
                            : 'mass-icon--inactive',
                    ]"
                    :aria-label="
                        data.visible
                            ? t('spa.cups.visible_yes')
                            : t('spa.cups.visible_no')
                    "
                    :title="
                        data.visible
                            ? t('spa.cups.visible_yes')
                            : t('spa.cups.visible_no')
                    "
                    role="img"
                />
            </span>
        </template>
        <template #cell-created="{ data }"
            ><ImpressionDetails
                :impression="data.created"
                :users="[]"
                :label="t('spa.cups.created')"
        /></template>
        <template #cell-updated="{ data }"
            ><ImpressionDetails
                :impression="data.updated"
                :users="[]"
                :label="t('spa.cups.updated')"
        /></template>
        <template #cell-actions="{ data }">
            <CupActionMenu
                :cup-id="data.id"
                :table-url="cupTableUrl(data)"
                @delete="selectedCup = data"
            />
        </template>
    </ListingTable>
    <ConfirmDeleteDialog
        v-if="auth.isAuthenticated && selectedCup"
        :visible="Boolean(selectedCup)"
        :title="t('spa.cups.delete.title')"
        :confirmation="t('spa.cups.delete.confirm', { name: selectedCup.name })"
        :cancel-label="t('spa.cups.delete.cancel')"
        :action-label="t('spa.cups.delete.action')"
        :pending="deleting"
        @cancel="selectedCup = null"
        @confirm="deleteSelectedCup"
    />
</template>
