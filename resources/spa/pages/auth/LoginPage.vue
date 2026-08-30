<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { t } from '../../i18n'
import Button from 'primevue/button'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'

const email = ref('')
const password = ref('')
const error = ref('')
const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

async function submit(): Promise<void> {
    error.value = ''
    try {
        await auth.login(email.value, password.value)
        await router.push(String(route.query.return || '/app/competitions'))
    } catch {
        error.value = t('spa.login.error')
    }
}
</script>

<template>
    <Card class="form-card auth-card">
        <template #title>{{ t('spa.login.title') }}</template>
        <template #content>
            <form class="spa-form" @submit.prevent="submit">
                <div class="form-field">
                    <label for="login-email">{{ t('spa.login.email') }}</label>
                    <InputText
                        id="login-email"
                        v-model="email"
                        type="email"
                        required
                    />
                </div>
                <div class="form-field">
                    <label for="login-password">{{
                        t('spa.login.password')
                    }}</label>
                    <InputText
                        id="login-password"
                        v-model="password"
                        type="password"
                        required
                    />
                </div>
                <Button type="submit" :label="t('spa.login.submit')" />
                <Message v-if="error" severity="error" :closable="false">{{
                    error
                }}</Message>
            </form>
        </template>
    </Card>
</template>
