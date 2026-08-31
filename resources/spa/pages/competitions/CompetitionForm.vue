<script setup lang="ts">
import { reactive, watch } from 'vue'
import Button from 'primevue/button'
import Checkbox from 'primevue/checkbox'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import Textarea from 'primevue/textarea'
import { t } from '../../i18n'
import DateFilter from '../../components/DateFilter.vue'
import { isDateRangeValid } from './competitionModels'
import { competitionFormInitialValue } from './competitionFormModels'
import type { CreateCompetitionRequest } from '../../api/types'

const props = withDefaults(
    defineProps<{
        initialValue?: Partial<CreateCompetitionRequest>
        errors?: Record<string, string>
        submitLabel: string
        pending?: boolean
        error?: string
    }>(),
    { initialValue: () => ({}), errors: () => ({}), pending: false, error: '' },
)

const emit = defineEmits<{
    submit: [value: CreateCompetitionRequest]
}>()

const form = reactive(competitionFormInitialValue(props.initialValue))
const localErrors = reactive<Record<string, string>>({})

watch(
    () => props.initialValue,
    (value) => Object.assign(form, competitionFormInitialValue(value)),
)

function fieldError(field: string): string | undefined {
    return localErrors[field] ?? props.errors[field]
}

function submit(): void {
    Object.keys(localErrors).forEach((field) => delete localErrors[field])
    if (!isDateRangeValid(form.from, form.to)) {
        localErrors.to = t('spa.competition.create.date_order')
        return
    }

    emit('submit', { ...form })
}
</script>

<template>
    <form class="spa-form" @submit.prevent="submit">
        <div class="form-field">
            <label for="competition-name">{{
                t('spa.competition.create.name')
            }}</label>
            <InputText id="competition-name" v-model="form.name" required />
            <small v-if="fieldError('name')" class="field-error">{{
                fieldError('name')
            }}</small>
        </div>
        <div class="form-field">
            <label for="competition-description">{{
                t('spa.competition.create.description')
            }}</label>
            <Textarea
                id="competition-description"
                v-model="form.description"
                required
                rows="4"
            />
            <small v-if="fieldError('description')" class="field-error">{{
                fieldError('description')
            }}</small>
        </div>
        <div class="form-grid">
            <DateFilter
                v-model="form.from"
                class="form-field"
                input-id="competition-from"
                :label="t('spa.competition.create.from')"
                :error="fieldError('from')"
                required
            />
            <DateFilter
                v-model="form.to"
                class="form-field"
                input-id="competition-to"
                :label="t('spa.competition.create.to')"
                :error="fieldError('to')"
                required
            />
        </div>
        <div class="form-checkbox">
            <Checkbox v-model="form.mass" input-id="competition-mass" binary />
            <label for="competition-mass">{{
                t('spa.competition.create.mass')
            }}</label>
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
