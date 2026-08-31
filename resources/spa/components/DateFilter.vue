<script setup lang="ts">
import { ref, watch } from 'vue'
import InputText from 'primevue/inputtext'

const props = withDefaults(
    defineProps<{
        modelValue: string
        inputId: string
        label: string
        error?: string
        disabled?: boolean
    }>(),
    { disabled: false, error: '' },
)

const inputValue = ref(props.modelValue)

const emit = defineEmits<{
    'update:modelValue': [value: string]
}>()

watch(
    () => props.modelValue,
    (value) => {
        inputValue.value = value
    },
)

function onInput(value: string | undefined): void {
    inputValue.value = value ?? ''

    if (
        inputValue.value === '' ||
        /^\d{4}-\d{2}-\d{2}$/.test(inputValue.value)
    ) {
        emit('update:modelValue', inputValue.value)
    }
}
</script>

<template>
    <div class="filter-field">
        <label :for="inputId">{{ label }}</label>
        <InputText
            :id="inputId"
            v-model="inputValue"
            :disabled="disabled"
            type="text"
            inputmode="numeric"
            autocomplete="off"
            placeholder="YYYY-MM-DD"
            maxlength="10"
            @update:model-value="onInput"
        />
        <small v-if="error" class="field-error">{{ error }}</small>
    </div>
</template>
