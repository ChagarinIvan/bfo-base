<script setup lang="ts">
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import FilterPanel from '../../components/FilterPanel.vue'
import { t } from '../../i18n'
import { hasTooShortNameSearch } from '../listingModels'

defineProps<{
    name: string
    group: string
    hasPerson: boolean | null
    isEqual: boolean | null
}>()

const emit = defineEmits<{
    'update:name': [value: string]
    'update:group': [value: string]
    'update:hasPerson': [value: boolean | null]
    'update:isEqual': [value: boolean | null]
    'name-change': [value: string]
    'group-change': [value: string]
    'filter-change': []
}>()

const booleanOptions = [
    { label: t('spa.rank_check.all_options'), value: null },
    { label: t('spa.rank_check.yes'), value: true },
    { label: t('spa.rank_check.no'), value: false },
]

function onNameChange(value: string | undefined): void {
    emit('update:name', value ?? '')
    emit('name-change', value ?? '')
}

function onGroupChange(value: string | undefined): void {
    emit('update:group', value ?? '')
    emit('group-change', value ?? '')
}

function onHasPersonChange(value: boolean | null): void {
    emit('update:hasPerson', value)
    emit('filter-change')
}

function onEqualChange(value: boolean | null): void {
    emit('update:isEqual', value)
    emit('filter-change')
}
</script>

<template>
    <FilterPanel>
        <div class="filter-field">
            <label for="rank-check-name-filter">{{
                t('spa.rank_check.name')
            }}</label>
            <InputText
                id="rank-check-name-filter"
                :model-value="name"
                @update:model-value="onNameChange"
            />
            <small v-if="hasTooShortNameSearch(name)" class="filter-hint">
                {{ t('spa.person.search_hint') }}
            </small>
        </div>
        <div class="filter-field">
            <label for="rank-check-has-person-filter">{{
                t('spa.rank_check.has_person')
            }}</label>
            <Select
                id="rank-check-has-person-filter"
                :model-value="hasPerson"
                :options="booleanOptions"
                option-label="label"
                option-value="value"
                @update:model-value="onHasPersonChange"
            />
        </div>
        <div class="filter-field">
            <label for="rank-check-equal-filter">{{
                t('spa.rank_check.equal')
            }}</label>
            <Select
                id="rank-check-equal-filter"
                :model-value="isEqual"
                :options="booleanOptions"
                option-label="label"
                option-value="value"
                @update:model-value="onEqualChange"
            />
        </div>
        <div class="filter-field">
            <label for="rank-check-group-filter">{{
                t('spa.rank_check.group')
            }}</label>
            <InputText
                id="rank-check-group-filter"
                :model-value="group"
                @update:model-value="onGroupChange"
            />
            <small v-if="hasTooShortNameSearch(group)" class="filter-hint">
                {{ t('spa.person.search_hint') }}
            </small>
        </div>
    </FilterPanel>
</template>
