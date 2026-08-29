<script setup lang="ts">
import { onMounted, ref } from 'vue'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import { api } from '../../api/client'
import { useAuthStore } from '../../stores/auth'
import { t } from '../../i18n'
import type {
    ApiResponse,
    Competition,
    Impression,
    User,
} from '../../api/types'

const competitions = ref<Competition[]>([])
const users = ref<User[]>([])
const year = ref(new Date().getFullYear())
const loading = ref(false)
const error = ref('')
const auth = useAuthStore()

function userLabel(impression: Impression | undefined): string {
    if (!impression) return ''

    const user = users.value.find((item) => String(item.id) === impression.by)
    return (
        user?.name ||
        user?.email ||
        t('spa.competitions.unknown_user', { id: impression.by })
    )
}

async function load(): Promise<void> {
    loading.value = true
    error.value = ''
    try {
        competitions.value = (
            await api.get<ApiResponse<Competition[]>>('/competitions', {
                params: { year: year.value },
            })
        ).data.data

        if (auth.isAuthenticated && users.value.length === 0) {
            users.value = (
                await api.get<ApiResponse<User[]>>('/users')
            ).data.data
        }
    } catch {
        error.value = t('spa.competitions.error')
    } finally {
        loading.value = false
    }
}

onMounted(load)
</script>

<template>
    <h1>{{ t('spa.competitions.title') }}</h1>
    <label
        >{{ t('spa.competitions.year') }}
        <input v-model.number="year" type="number" @change="load"
    /></label>
    <p v-if="loading">{{ t('spa.competitions.loading') }}</p>
    <p v-else-if="error">{{ error }}</p>
    <p v-else-if="!competitions.length">{{ t('spa.competitions.empty') }}</p>
    <DataTable v-else :value="competitions" striped-rows>
        <Column field="name" :header="t('spa.competition.create.name')" />
        <Column
            field="description"
            :header="t('spa.competitions.description')"
        />
        <Column field="from" :header="t('spa.competitions.from')" />
        <Column field="to" :header="t('spa.competitions.to')" />
        <Column field="year" :header="t('spa.competitions.year')" />
        <Column field="mass" :header="t('spa.competitions.mass')" />
        <Column
            v-if="auth.isAuthenticated"
            :header="t('spa.competitions.created')"
        >
            <template #body="{ data }">{{ userLabel(data.created) }}</template>
        </Column>
        <Column
            v-if="auth.isAuthenticated"
            :header="t('spa.competitions.updated')"
        >
            <template #body="{ data }">{{ userLabel(data.updated) }}</template>
        </Column>
    </DataTable>
</template>
