<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Message from 'primevue/message'
import { activateRegistrationInvitation } from '../../api/auth'
import { t } from '../../i18n'

const route = useRoute()
const loading = ref(true)
const error = ref('')
const activated = ref(false)

onMounted(async () => {
    try {
        await activateRegistrationInvitation(String(route.params.token))
        activated.value = true
    } catch {
        error.value = t('spa.registration.activation_error')
    } finally {
        loading.value = false
    }
})
</script>

<template>
    <Card class="form-card auth-card">
        <template #title>{{ t('spa.registration.activation_title') }}</template>
        <template #content>
            <Message v-if="loading" severity="info" :closable="false">
                {{ t('spa.registration.activating') }}
            </Message>
            <template v-else>
                <Message v-if="activated" severity="success" :closable="false">
                    {{ t('spa.registration.activated') }}
                </Message>
                <Message v-else severity="error" :closable="false">
                    {{ error }}
                </Message>
                <RouterLink v-if="activated" to="/app/login">
                    <Button :label="t('spa.nav.login')" />
                </RouterLink>
            </template>
        </template>
    </Card>
</template>
