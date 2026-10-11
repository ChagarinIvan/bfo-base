<script setup lang="ts">
import { reactive, ref, watch } from 'vue'
import Button from 'primevue/button'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import type { PageState } from 'primevue/paginator'
import Select from 'primevue/select'
import type {
    CupEventFormRequest,
    Event,
    PaginationHeaders,
} from '../../api/types'
import SlicePaginator from '../../components/SlicePaginator.vue'
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
        pagination?: PaginationHeaders
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
        pagination: undefined,
        optionsLoading: false,
        optionsError: '',
    },
)

const emit = defineEmits<{
    submit: [value: CupEventFormRequest]
    'update:search': [value: string]
    page: [event: PageState]
}>()
const form = reactive<CupEventFormRequest>({ eventId: 0, points: 0 })
const selectedOption = ref<Event | null>(null)

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

function submit(): void {
    emit('submit', { ...form, eventId: Number(form.eventId) })
}
</script>

<template>
    <form class="spa-form" @submit.prevent="submit">
        <div v-if="remoteSearch" class="form-field">
            <label for="cup-event-search">{{
                t('spa.cup_event.form.search')
            }}</label>
            <InputText
                id="cup-event-search"
                :model-value="search"
                :placeholder="t('spa.cup_event.form.search_placeholder')"
                @update:model-value="
                    emit('update:search', String($event ?? ''))
                "
            />
            <small v-if="hasTooShortNameSearch(search)" class="filter-hint">
                {{ t('spa.competitions.name_hint') }}
            </small>
        </div>
        <div class="form-field">
            <label for="cup-event-event">{{
                t('spa.cup_event.form.event')
            }}</label>
            <Select
                id="cup-event-event"
                v-model="form.eventId"
                :options="events"
                option-value="id"
                :filter="!remoteSearch"
                :filter-fields="['name', 'competitionName']"
                :placeholder="t('spa.cup_event.form.event_placeholder')"
                :invalid="Boolean(errors.eventId)"
                required
            >
                <template #value="{ value, placeholder }">
                    {{
                        selectedEvent(value)
                            ? label(selectedEvent(value)!)
                            : placeholder
                    }}
                </template>
                <template #option="{ option }">{{ label(option) }}</template>
            </Select>
            <small v-if="errors.eventId" class="field-error">{{
                errors.eventId
            }}</small>
            <Message
                v-if="remoteSearch && optionsLoading"
                severity="info"
                :closable="false"
                >{{ t('spa.cup_event.form.options_loading') }}</Message
            >
            <Message
                v-else-if="remoteSearch && optionsError"
                severity="error"
                :closable="false"
                >{{ optionsError }}</Message
            >
            <Message
                v-else-if="
                    remoteSearch &&
                    !events.length &&
                    !hasTooShortNameSearch(search)
                "
                severity="secondary"
                :closable="false"
                >{{ t('spa.cup_event.form.options_empty') }}</Message
            >
            <SlicePaginator
                v-if="
                    remoteSearch && pagination && !hasTooShortNameSearch(search)
                "
                :pagination="pagination"
                :rows-per-page-options="[20]"
                @page="emit('page', $event)"
            />
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
