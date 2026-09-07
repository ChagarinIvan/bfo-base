<script setup lang="ts">
import { computed } from 'vue'
import Select from 'primevue/select'
import type { ClubOption } from '../api/types'
import { t } from '../i18n'

const props = withDefaults(
    defineProps<{
        modelValue: string | null
        clubs: ClubOption[]
        inputId: string
        label: string
        includeAll?: boolean
        clearable?: boolean
    }>(),
    { includeAll: false, clearable: false },
)

const emit = defineEmits<{
    'update:modelValue': [value: string | null]
}>()

const options = computed(() => [
    ...(props.includeAll
        ? [{ id: '', name: t('spa.person.all_options') }]
        : []),
    ...props.clubs,
])
</script>

<template>
    <div>
        <label :for="inputId">{{ label }}</label>
        <Select
            :id="inputId"
            :model-value="modelValue"
            :options="options"
            option-label="name"
            option-value="id"
            :show-clear="clearable"
            @update:model-value="emit('update:modelValue', $event)"
        />
    </div>
</template>
