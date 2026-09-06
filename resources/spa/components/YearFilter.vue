<script setup lang="ts">
import { computed } from 'vue'
import Select from 'primevue/select'
import { t } from '../i18n'

const props = withDefaults(
    defineProps<{
        modelValue: number | null
        years: number[]
        inputId: string
        disabled?: boolean
    }>(),
    { disabled: false },
)

const emit = defineEmits<{ 'update:modelValue': [value: number | null] }>()

const options = computed(() =>
    props.years.map((year) => ({ label: String(year), value: year })),
)
</script>

<template>
    <div class="filter-field">
        <label :for="inputId">{{ t('spa.competitions.year') }}</label>
        <Select
            :id="inputId"
            :model-value="modelValue"
            :options="options"
            option-label="label"
            option-value="value"
            :placeholder="t('spa.competitions.year_placeholder')"
            filter
            filter-match-mode="contains"
            :filter-placeholder="t('spa.competitions.year_filter')"
            :disabled="disabled"
            @update:model-value="emit('update:modelValue', $event)"
        />
    </div>
</template>
