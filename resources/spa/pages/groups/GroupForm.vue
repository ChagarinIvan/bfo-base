<script setup lang="ts">
import { reactive, watch } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import type { UpdateGroupRequest } from '../../api/types'
import { t } from '../../i18n'
const props = defineProps<{
    initialValue: UpdateGroupRequest
    pending?: boolean
    error?: string
}>()
const emit = defineEmits<{ submit: [value: UpdateGroupRequest] }>()
const form = reactive<UpdateGroupRequest>({ name: '' })
watch(
    () => props.initialValue,
    (value) => {
        form.name = value.name
    },
    { immediate: true },
)
</script>
<template>
    <form
        class="spa-form"
        @submit.prevent="emit('submit', { name: form.name.trim() })"
    >
        <div class="form-field">
            <label for="group-name">{{ t('spa.groups.name') }}</label
            ><InputText id="group-name" v-model="form.name" required />
        </div>
        <Button
            type="submit"
            :label="t('spa.group.save')"
            :loading="pending"
            severity="success"
        /><Message v-if="error" severity="error" :closable="false">{{
            error
        }}</Message>
    </form>
</template>
