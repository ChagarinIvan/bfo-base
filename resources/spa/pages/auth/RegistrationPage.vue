<script setup lang="ts">
import { ref } from 'vue'
import Button from 'primevue/button'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import { sendRegistrationInvitation } from '../../api/auth'
import { t } from '../../i18n'

const email = ref('')
const error = ref('')
const sent = ref(false)

async function submit(): Promise<void> {
    error.value = ''
    sent.value = false

    try {
        await sendRegistrationInvitation(email.value)
        sent.value = true
    } catch {
        error.value = t('spa.registration.error')
    }
}
</script>

<template>
    <Card class="form-card auth-card">
        <template #title>{{ t('spa.registration.title') }}</template>
        <template #content>
            <form class="spa-form" @submit.prevent="submit">
                <div class="form-field">
                    <label for="registration-email">{{
                        t('spa.registration.email')
                    }}</label>
                    <InputText
                        id="registration-email"
                        v-model="email"
                        type="email"
                        required
                    />
                </div>
                <Button type="submit" :label="t('spa.registration.submit')" />
                <Message v-if="sent" severity="success" :closable="false">
                    {{ t('spa.registration.sent') }}
                </Message>
                <Message v-if="error" severity="error" :closable="false">
                    {{ error }}
                </Message>
            </form>
        </template>
    </Card>
</template>
