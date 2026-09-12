<script setup lang="ts">
import { ref, watch } from 'vue'
import DatePicker from 'primevue/datepicker'

const props = withDefaults(
    defineProps<{
        modelValue: string
        inputId: string
        label: string
        error?: string
        required?: boolean
        disabled?: boolean
    }>(),
    { disabled: false, error: '', required: false },
)

const inputValue = ref(parseDate(props.modelValue))

const emit = defineEmits<{
    'update:modelValue': [value: string]
}>()

watch(
    () => props.modelValue,
    (value) => {
        inputValue.value = parseDate(value)
    },
)

function parseDate(value: string): Date | null {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value)
    if (!match) return null

    const date = new Date(
        Number(match[1]),
        Number(match[2]) - 1,
        Number(match[3]),
    )

    return date.getFullYear() === Number(match[1]) &&
        date.getMonth() === Number(match[2]) - 1 &&
        date.getDate() === Number(match[3])
        ? date
        : null
}

function formatDate(value: Date): string {
    const year = value.getFullYear()
    const month = String(value.getMonth() + 1).padStart(2, '0')
    const day = String(value.getDate()).padStart(2, '0')

    return [year, month, day].join('-')
}

function onInput(
    value: Date | Date[] | Array<Date | null> | null | undefined,
): void {
    if (!(value instanceof Date)) {
        inputValue.value = null
        emit('update:modelValue', '')
        return
    }

    inputValue.value = value
    emit('update:modelValue', formatDate(value))
}
</script>

<template>
    <div class="filter-field">
        <label :for="inputId">{{ label }}</label>
        <DatePicker
            :id="inputId"
            v-model="inputValue"
            :disabled="disabled"
            :required="required"
            date-format="yy-mm-dd"
            placeholder="YYYY-MM-DD"
            show-icon
            @update:model-value="onInput"
        />
        <small v-if="error" class="field-error">{{ error }}</small>
    </div>
</template>
