<script setup lang="ts">
import { useAuthStore } from '../stores/auth'
import { t } from '../i18n'
import ActionButton from './actions/ActionButton.vue'

const props = defineProps<{ personId: string }>()
const auth = useAuthStore()
</script>

<template>
    <div class="details-actions">
        <ActionButton
            v-if="auth.isAuthenticated"
            as="a"
            :href="`/app/persons/${props.personId}/edit`"
            :label="t('spa.person.edit')"
            icon="pi pi-pencil"
            severity="secondary"
        />
        <RouterLink
            v-slot="{ navigate, isExactActive }"
            :to="`/app/persons/${props.personId}`"
            custom
        >
            <ActionButton
                type="button"
                :class="{ 'person-info-tab-active': isExactActive }"
                :label="t('spa.person.events_count')"
                icon="pi pi-trophy"
                severity="contrast"
                @click="navigate"
            />
        </RouterLink>
        <RouterLink
            v-if="auth.isAuthenticated"
            v-slot="{ navigate, isActive }"
            :to="`/app/persons/${props.personId}/prompts`"
            custom
        >
            <ActionButton
                type="button"
                :class="{ 'person-info-tab-active': isActive }"
                :label="t('spa.person.prompts')"
                icon="pi pi-comments"
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
            <ActionButton
                type="button"
                :class="{ 'person-info-tab-active': isActive }"
                :label="t('spa.person.payments')"
                icon="pi pi-dollar"
                severity="warn"
                @click="navigate"
            />
        </RouterLink>
        <RouterLink
            v-slot="{ navigate, isActive }"
            :to="`/app/persons/${props.personId}/ranks`"
            custom
        >
            <ActionButton
                type="button"
                :class="{ 'person-info-tab-active': isActive }"
                :label="t('spa.person.ranks')"
                icon="pi pi-stopwatch"
                severity="info"
                @click="navigate"
            />
        </RouterLink>
    </div>
</template>
