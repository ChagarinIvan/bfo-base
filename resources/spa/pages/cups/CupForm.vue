<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue'
import Button from 'primevue/button'
import Checkbox from 'primevue/checkbox'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import Select from 'primevue/select'
import { t } from '../../i18n'
import { getYears } from '../../api/years'
import type { CupFormRequest } from '../../api/types'
import CupTypeIcon from '../../components/CupTypeIcon.vue'
import { CUP_TYPES, cupTypeDefinition } from '../../components/cupTypeModels'

const types = CUP_TYPES.map((value) => ({
    value,
    label: t(cupTypeDefinition(value).label),
}))

const props = withDefaults(
    defineProps<{
        initialValue?: Partial<Omit<CupFormRequest, 'eventsCount'>> & {
            eventsCount?: number | string
        }
        errors?: Record<string, string>
        submitLabel: string
        pending?: boolean
        error?: string
    }>(),
    { initialValue: () => ({}), errors: () => ({}), pending: false, error: '' },
)

const emit = defineEmits<{ submit: [value: CupFormRequest] }>()
const availableYears = ref<number[]>([])
const yearOptions = computed(() =>
    availableYears.value.map((year) => ({ label: String(year), value: year })),
)
const form = reactive<CupFormRequest>({
    name: '',
    eventsCount: 1,
    year: new Date().getFullYear(),
    type: 'master',
    visible: true,
})

watch(
    () => props.initialValue,
    (value) => {
        Object.assign(form, {
            name: value?.name ?? '',
            eventsCount: Number(value?.eventsCount ?? 1),
            year: value?.year ?? new Date().getFullYear(),
            type: value?.type ?? 'master',
            visible: value?.visible ?? true,
        })
    },
    { immediate: true },
)

function submit(): void {
    emit('submit', { ...form, name: form.name.trim() })
}

onMounted(async () => {
    try {
        availableYears.value = await getYears()
    } catch {
        availableYears.value = []
    }
})
</script>

<template>
    <form class="spa-form" @submit.prevent="submit">
        <div class="form-field">
            <label for="cup-name">{{ t('spa.cup.form.name') }}</label>
            <InputText
                id="cup-name"
                v-model="form.name"
                required
                :invalid="Boolean(errors.name)"
            />
            <small v-if="errors.name" class="field-error">{{
                errors.name
            }}</small>
        </div>
        <div class="form-grid">
            <div class="form-field">
                <label for="cup-type">{{ t('spa.cup.type') }}</label>
                <Select
                    id="cup-type"
                    v-model="form.type"
                    :options="types"
                    option-label="label"
                    option-value="value"
                >
                    <template #option="{ option }">
                        <CupTypeIcon :type="option.value" show-label />
                    </template>
                    <template #value="{ value }">
                        <CupTypeIcon v-if="value" :type="value" show-label />
                    </template>
                </Select>
                <small v-if="errors.type" class="field-error">{{
                    errors.type
                }}</small>
            </div>
            <div class="form-field">
                <label for="cup-events-count">{{
                    t('spa.cup.form.events_count')
                }}</label>
                <InputNumber
                    id="cup-events-count"
                    v-model="form.eventsCount"
                    :min="1"
                    :max="100"
                    required
                    :invalid="Boolean(errors.eventsCount)"
                />
                <small v-if="errors.eventsCount" class="field-error">{{
                    errors.eventsCount
                }}</small>
            </div>
            <div class="form-field">
                <label for="cup-year">{{ t('spa.cup.form.year') }}</label>
                <Select
                    id="cup-year"
                    v-model="form.year"
                    :options="yearOptions"
                    option-label="label"
                    option-value="value"
                    :invalid="Boolean(errors.year)"
                />
                <small v-if="errors.year" class="field-error">{{
                    errors.year
                }}</small>
            </div>
        </div>
        <div class="form-checkbox">
            <Checkbox v-model="form.visible" input-id="cup-visible" binary />
            <label for="cup-visible">{{ t('spa.cup.form.visible') }}</label>
        </div>
        <Button
            type="submit"
            :label="submitLabel"
            severity="success"
            :loading="pending"
        />
        <Message v-if="error" severity="error" :closable="false">{{
            error
        }}</Message>
    </form>
</template>
