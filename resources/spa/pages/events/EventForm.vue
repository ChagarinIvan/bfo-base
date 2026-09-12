<script setup lang="ts">
import { reactive, ref, watch } from 'vue'
import Button from 'primevue/button'
import FileUpload from 'primevue/fileupload'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import Textarea from 'primevue/textarea'
import type { EventFormRequest } from '../../api/types'

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
    (value) => Object.assign(form, { protocol: null, url: '', ...value }),
    { immediate: true },
)

function fieldError(field: string): string | undefined {
    return props.errors[field]
}

function selectProtocol(event: { files: File[] }): void {
    form.protocol = event.files[0] ?? null
    if (form.protocol) form.url = ''
}

function submit(): void {
    localError.value = ''
    if (props.sourceRequired && !form.protocol && !form.url) {
        localError.value = 'Дадайце файл пратаколу або спасылку.'
        return
    }
    emit('submit', { ...form })
}
</script>

<template>
    <form class="spa-form" @submit.prevent="submit">
        <div class="form-field">
            <label for="event-name">Назва</label>
            <InputText id="event-name" v-model="form.name" required />
            <small v-if="fieldError('name')" class="field-error">{{
                fieldError('name')
            }}</small>
        </div>
        <div class="form-field">
            <label for="event-description">Апісанне</label>
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
        <div class="form-field">
            <label for="event-date">Дата</label>
            <InputText
                id="event-date"
                v-model="form.date"
                type="date"
                required
            />
            <small v-if="fieldError('date')" class="field-error">{{
                fieldError('date')
            }}</small>
        </div>
        <div class="form-field">
            <label>Пратакол</label>
            <FileUpload
                mode="basic"
                name="protocol"
                choose-label="Выбраць файл"
                :auto="false"
                @select="selectProtocol"
            />
            <small v-if="form.protocol">{{ form.protocol.name }}</small>
            <small v-if="fieldError('protocol')" class="field-error">{{
                fieldError('protocol')
            }}</small>
        </div>
        <div class="form-field">
            <label for="event-url">Спасылка OBelarus.net</label>
            <InputText
                id="event-url"
                v-model="form.url"
                :disabled="Boolean(form.protocol)"
            />
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
