<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import Button from 'primevue/button'
import InputNumber from 'primevue/inputnumber'
import Message from 'primevue/message'
import Select from 'primevue/select'
import type { CupEventFormRequest, Event } from '../../api/types'
import { t } from '../../i18n'
import { hasTooShortNameSearch } from '../listingModels'

const props = withDefaults(
    defineProps<{
        events: Event[]
        initialValue?: Partial<CupEventFormRequest>
        errors?: Record<string, string>
        submitLabel: string
        pending?: boolean
        error?: string
        remoteSearch?: boolean
        search?: string
        optionsLoading?: boolean
        optionsError?: string
    }>(),
    {
        initialValue: () => ({}),
        errors: () => ({}),
        pending: false,
        error: '',
        remoteSearch: false,
        search: '',
        optionsLoading: false,
        optionsError: '',
    },
)

const emit = defineEmits<{
    submit: [value: CupEventFormRequest]
    'update:search': [value: string]
}>()
const form = reactive<CupEventFormRequest>({ eventId: 0, points: 0 })
const selectedOption = ref<Event | null>(null)
const selectOptions = computed(() =>
    props.remoteSearch
        ? props.events.map((event) => ({
              ...event,
              remoteSearchValue: props.search,
          }))
        : props.events,
)

watch(
    () => props.initialValue,
    (value) =>
        Object.assign(form, {
            eventId: value?.eventId ?? 0,
            points: value?.points ?? 0,
        }),
    { immediate: true },
)

watch(
    [() => form.eventId, () => props.events],
    ([id, events]) => {
        const option = events.find((event) => event.id === String(id))
        if (option) {
            selectedOption.value = option
        } else if (selectedOption.value?.id !== String(id)) {
            selectedOption.value = null
        }
    },
    { immediate: true },
)

function selectedEvent(value: unknown): Event | undefined {
    return (
        props.events.find((event) => event.id === String(value)) ??
        (selectedOption.value?.id === String(value)
            ? selectedOption.value
            : undefined)
    )
}

function label(event: Event): string {
    return `${event.competitionName ?? ''} — ${event.name}`
}

function onFilter(event: { value: string }): void {
    if (props.remoteSearch) emit('update:search', event.value)
}

function emptyOptionsMessage(): string {
    if (props.optionsLoading) return t('spa.cup_event.form.options_loading')
    if (props.optionsError) return props.optionsError
    if (hasTooShortNameSearch(props.search))
        return t('spa.competitions.name_hint')

    return t('spa.cup_event.form.options_empty')
}

function submit(): void {
    emit('submit', { ...form, eventId: Number(form.eventId) })
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
                :options="selectOptions"
                option-value="id"
                filter
                :filter-fields="
                    remoteSearch
                        ? ['remoteSearchValue']
                        : ['name', 'competitionName']
                "
                :filter-placeholder="
                    remoteSearch
                        ? t('spa.cup_event.form.search_placeholder')
                        : undefined
                "
                :loading="remoteSearch && optionsLoading"
                :placeholder="t('spa.cup_event.form.event_placeholder')"
                :invalid="Boolean(errors.eventId)"
                required
                @filter="onFilter"
            >
                <template #value="{ value, placeholder }">
                    {{
                        selectedEvent(value)
                            ? label(selectedEvent(value)!)
                            : placeholder
                    }}
                </template>
                <template #option="{ option }">{{ label(option) }}</template>
                <template v-if="remoteSearch" #empty>{{
                    emptyOptionsMessage()
                }}</template>
                <template v-if="remoteSearch" #emptyfilter>{{
                    emptyOptionsMessage()
                }}</template>
            </Select>
            <small v-if="errors.eventId" class="field-error">{{
                errors.eventId
            }}</small>
            <small
                v-if="remoteSearch && hasTooShortNameSearch(search)"
                class="filter-hint"
            >
                {{ t('spa.competitions.name_hint') }}
            </small>
            <Message
                v-if="remoteSearch && optionsError"
                severity="error"
                :closable="false"
                >{{ optionsError }}</Message
            >
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
