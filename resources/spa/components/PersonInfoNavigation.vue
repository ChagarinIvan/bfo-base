<script setup lang="ts">
import Button from 'primevue/button'
import { useAuthStore } from '../stores/auth'
import { t } from '../i18n'

const props = defineProps<{ personId: string }>()
const auth = useAuthStore()
</script>

<template>
    <div class="person-info-actions">
        <Button
            v-if="auth.isAuthenticated"
            as="a"
            :href="`/persons/${props.personId}/edit`"
            :label="t('spa.person.edit')"
            icon="pi pi-pencil"
            severity="secondary"
        />
        <RouterLink
            v-if="auth.isAuthenticated"
            v-slot="{ navigate, isActive }"
            :to="`/app/persons/${props.personId}/prompts`"
            custom
        >
            <Button
                type="button"
                :class="{ 'person-info-tab-active': isActive }"
                :label="t('spa.person.prompts')"
                icon="pi pi-terminal"
                severity="success"
                @click="navigate"
            />
        </RouterLink>
        <RouterLink
            v-if="auth.isAuthenticated"
            v-slot="{ navigate, isActive }"
            :to="`/app/persons/${props.personId}/payments`"
            custom
        >
            <Button
                type="button"
                :class="{ 'person-info-tab-active': isActive }"
                :label="t('spa.person.payments')"
                icon="pi pi-dollar"
                severity="warn"
                @click="navigate"
            />
        </RouterLink>
        <Button
            as="a"
            :href="`/ranks/person/${props.personId}`"
            :label="t('spa.person.ranks')"
            icon="pi pi-stopwatch"
            severity="info"
        />
    </div>
</template>
