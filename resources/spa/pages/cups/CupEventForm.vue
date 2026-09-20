<script setup lang="ts">
import { reactive, watch } from 'vue'
import Button from 'primevue/button'
import InputNumber from 'primevue/inputnumber'
import Message from 'primevue/message'
import Select from 'primevue/select'
import type { CupEventFormRequest, Event } from '../../api/types'
import { t } from '../../i18n'

const props = withDefaults(
    defineProps<{
        events: Event[]
        initialValue?: Partial<CupEventFormRequest>
        errors?: Record<string, string>
        submitLabel: string
        pending?: boolean
        error?: string
    }>(),
    { initialValue: () => ({}), errors: () => ({}), pending: false, error: '' },
)

const emit = defineEmits<{ submit: [value: CupEventFormRequest] }>()
const form = reactive<CupEventFormRequest>({ eventId: 0, points: 0 })

watch(
    () => props.initialValue,
    (value) =>
        Object.assign(form, {
            eventId: value?.eventId ?? 0,
            points: value?.points ?? 0,
        }),
    { immediate: true },
)

function label(event: Event): string {
    return `${event.competitionName ?? ''} — ${event.name}`
}

function submit(): void {
    emit('submit', { ...form })
}
</script>

<template>
    <form class="spa-form" @submit.prevent="submit">
        <div class="form-field">
            <label for="cup-event-event">{{
                t('spa.cup_event.form.event')
            }}</label>
            <Select
                id="cup-event-event"
                v-model="form.eventId"
                :options="events"
                option-value="id"
                :filter="true"
                :filter-fields="['name', 'competitionName']"
                :placeholder="t('spa.cup_event.form.event_placeholder')"
                :invalid="Boolean(errors.eventId)"
                required
            >
                <template #value="{ value, placeholder }">
                    {{
                        events.find((event) => event.id === String(value))
                            ? label(
                                  events.find(
                                      (event) => event.id === String(value),
                                  )!,
                              )
                            : placeholder
                    }}
                </template>
                <template #option="{ option }">{{ label(option) }}</template>
            </Select>
            <small v-if="errors.eventId" class="field-error">{{
                errors.eventId
            }}</small>
        </div>
        <div class="form-field">
            <label for="cup-event-points">{{
                t('spa.cup_event.form.points')
            }}</label>
            <InputNumber
                id="cup-event-points"
                v-model="form.points"
                :min="0"
                :invalid="Boolean(errors.points)"
                required
            />
            <small v-if="errors.points" class="field-error">{{
                errors.points
            }}</small>
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
