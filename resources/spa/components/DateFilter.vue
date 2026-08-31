<script setup lang="ts">
import InputText from 'primevue/inputtext'

withDefaults(
    defineProps<{
        modelValue: string
        inputId: string
        label: string
        disabled?: boolean
    }>(),
    { disabled: false },
)

const emit = defineEmits<{
    'update:modelValue': [value: string]
}>()
</script>

<template>
    <div class="filter-field">
        <label :for="inputId">{{ label }}</label>
        <InputText
            :id="inputId"
            :model-value="modelValue"
            :disabled="disabled"
            type="text"
            inputmode="numeric"
            autocomplete="off"
            placeholder="YYYY-MM-DD"
            maxlength="10"
            @update:model-value="emit('update:modelValue', $event ?? '')"
        />
    </div>
</template>
