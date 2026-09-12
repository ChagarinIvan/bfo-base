<script setup lang="ts">
import { reactive, ref, watch } from 'vue'
import Button from 'primevue/button'
import FileUpload from 'primevue/fileupload'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import SelectButton from 'primevue/selectbutton'
import Textarea from 'primevue/textarea'
import type { EventFormRequest } from '../../api/types'
import { t } from '../../i18n'
import DateFilter from '../../components/DateFilter.vue'

const props = withDefaults(
    defineProps<{
        initialValue?: Partial<EventFormRequest>
        errors?: Record<string, string>
        submitLabel: string
        pending?: boolean
        error?: string
        sourceRequired?: boolean
    }>(),
    {
        initialValue: () => ({}),
        errors: () => ({}),
        pending: false,
        error: '',
        sourceRequired: false,
    },
)

const emit = defineEmits<{ submit: [value: EventFormRequest] }>()
type ProtocolSource = 'file' | 'url'

const protocolSource = ref<ProtocolSource>('file')
const protocolSourceOptions = [
    { label: t('spa.event.form.source_file'), value: 'file' as const },
    { label: t('spa.event.form.source_url'), value: 'url' as const },
]
const form = reactive<EventFormRequest>({
    name: '',
    description: '',
    date: '',
    protocol: null,
    url: '',
})
const localError = ref('')

watch(
    () => props.initialValue,
    (value) => {
        Object.assign(form, { protocol: null, url: '', ...value })
        if (value?.protocol) protocolSource.value = 'file'
        else if (value?.url) protocolSource.value = 'url'
    },
    { immediate: true },
)

function fieldError(field: string): string | undefined {
    return props.errors[field]
}

function selectProtocol(event: { files: File[] }): void {
    protocolSource.value = 'file'
    form.protocol = event.files[0] ?? null
}

function submit(): void {
    localError.value = ''
    const sourceValue =
        protocolSource.value === 'file' ? form.protocol : form.url
    if (props.sourceRequired && !sourceValue) {
        localError.value = t('spa.event.form.source_required')
        return
    }
    emit('submit', {
        ...form,
        protocol: protocolSource.value === 'file' ? form.protocol : null,
        url: protocolSource.value === 'url' ? form.url : '',
    })
}
</script>

<template>
    <form class="spa-form" @submit.prevent="submit">
        <div class="form-field">
            <label for="event-name">{{ t('spa.event.form.name') }}</label>
            <InputText id="event-name" v-model="form.name" required />
            <small v-if="fieldError('name')" class="field-error">{{
                fieldError('name')
            }}</small>
        </div>
        <div class="form-field">
            <label for="event-description">{{
                t('spa.event.form.description')
            }}</label>
            <Textarea
                id="event-description"
                v-model="form.description"
                rows="4"
                required
            />
            <small v-if="fieldError('description')" class="field-error">{{
                fieldError('description')
            }}</small>
        </div>
        <DateFilter
            v-model="form.date"
            class="form-field"
            input-id="event-date"
            :label="t('spa.event.form.date')"
            :error="fieldError('date')"
            required
        />
        <div class="form-field">
            <label>{{ t('spa.event.form.source') }}</label>
            <SelectButton
                v-model="protocolSource"
                :options="protocolSourceOptions"
                option-label="label"
                option-value="value"
                :allow-empty="false"
            />
        </div>
        <div v-if="protocolSource === 'file'" class="form-field">
            <label>{{ t('spa.event.form.protocol') }}</label>
            <FileUpload
                mode="basic"
                name="protocol"
                :choose-label="t('spa.event.form.choose_file')"
                :auto="false"
                @select="selectProtocol"
            />
            <small v-if="form.protocol">{{ form.protocol.name }}</small>
            <small v-if="fieldError('protocol')" class="field-error">{{
                fieldError('protocol')
            }}</small>
        </div>
        <div v-else class="form-field">
            <label for="event-url">{{ t('spa.event.form.url') }}</label>
            <InputText id="event-url" v-model="form.url" />
            <small v-if="fieldError('url')" class="field-error">{{
                fieldError('url')
            }}</small>
        </div>
        <Button
            type="submit"
            :label="submitLabel"
            severity="success"
            :loading="pending"
        />
        <Message
            v-if="localError || error"
            severity="error"
            :closable="false"
            >{{ localError || error }}</Message
        >
    </form>
</template>
