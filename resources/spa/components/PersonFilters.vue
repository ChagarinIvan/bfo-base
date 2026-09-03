<script setup lang="ts">
import { computed } from 'vue'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import type { RankOption } from '../api/ranks'
import type { ClubOption } from '../api/types'
import { t } from '../i18n'
import {
    birthYearOptions,
    hasTooShortNameSearch,
} from '../pages/persons/personsModels'
import FilterPanel from './FilterPanel.vue'

const props = withDefaults(
    defineProps<{
        name: string
        clubId?: string
        rankId: number | null
        birthYear: number | null
        clubs?: ClubOption[]
        ranks: RankOption[]
        fieldErrors?: Record<string, string>
        showClub?: boolean
        idPrefix?: string
    }>(),
    {
        clubId: '',
        clubs: () => [],
        fieldErrors: () => ({}),
        showClub: false,
        idPrefix: 'person',
    },
)

const emit = defineEmits<{
    'update:name': [value: string]
    'update:clubId': [value: string]
    'update:rankId': [value: number | null]
    'update:birthYear': [value: number | null]
    'name-change': [value: string | undefined]
    'filter-change': []
}>()

const yearOptions = computed(() =>
    birthYearOptions().map((year) => ({ label: String(year), value: year })),
)
const clubOptions = computed(() => [
    { id: '', name: t('spa.person.all_options') },
    ...props.clubs,
])
const rankOptions = computed(() => [
    { id: null, label: t('spa.person.all_options') },
    ...props.ranks,
])

function onNameChange(value: string | undefined): void {
    emit('update:name', value ?? '')
    emit('name-change', value)
}

function onClubChange(value: string): void {
    emit('update:clubId', value)
    emit('filter-change')
}

function onRankChange(value: number | null): void {
    emit('update:rankId', value)
    emit('filter-change')
}

function onBirthYearChange(value: number | null): void {
    emit('update:birthYear', value)
    emit('filter-change')
}
</script>

<template>
    <FilterPanel>
        <div class="filter-field">
            <label :for="`${idPrefix}-name-filter`">{{
                t('spa.person.search')
            }}</label>
            <InputText
                :id="`${idPrefix}-name-filter`"
                :model-value="name"
                :invalid="Boolean(fieldErrors.name)"
                @update:model-value="onNameChange"
            />
            <small v-if="fieldErrors.name" class="p-error">{{
                fieldErrors.name
            }}</small>
            <small v-else-if="hasTooShortNameSearch(name)" class="filter-hint">
                {{ t('spa.person.search_hint') }}
            </small>
        </div>
        <div v-if="showClub" class="filter-field">
            <label :for="`${idPrefix}-club-filter`">{{
                t('spa.person.club_filter')
            }}</label>
            <Select
                :id="`${idPrefix}-club-filter`"
                :model-value="clubId"
                :options="clubOptions"
                option-label="name"
                option-value="id"
                @update:model-value="onClubChange"
            />
        </div>
        <div class="filter-field">
            <label :for="`${idPrefix}-rank-filter`">{{
                t('spa.person.rank_filter')
            }}</label>
            <Select
                :id="`${idPrefix}-rank-filter`"
                :model-value="rankId"
                :options="rankOptions"
                option-label="label"
                option-value="id"
                @update:model-value="onRankChange"
            />
        </div>
        <div class="filter-field">
            <label :for="`${idPrefix}-birth-year-filter`">{{
                t('spa.person.birth_year_filter')
            }}</label>
            <Select
                :id="`${idPrefix}-birth-year-filter`"
                :model-value="birthYear"
                :options="[
                    { label: t('spa.person.all_options'), value: null },
                    ...yearOptions,
                ]"
                option-label="label"
                option-value="value"
                filter
                @update:model-value="onBirthYearChange"
            />
        </div>
    </FilterPanel>
</template>
