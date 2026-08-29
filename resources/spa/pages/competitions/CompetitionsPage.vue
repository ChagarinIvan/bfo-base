<script setup lang="ts">
import { onMounted, ref } from 'vue'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import Button from 'primevue/button'
import Card from 'primevue/card'
import InputNumber from 'primevue/inputnumber'
import Message from 'primevue/message'
import Toolbar from 'primevue/toolbar'
import { useRouter } from 'vue-router'
import { api } from '../../api/client'
import { useAuthStore } from '../../stores/auth'
import { t } from '../../i18n'
import {
    competitionQuery,
    formatDateRange,
    shouldLoadUsers,
} from './competitionModels'
import type { Competition, Impression, User } from '../../api/types'

const competitions = ref<Competition[]>([])
const users = ref<User[]>([])
const year = ref(new Date().getFullYear())
const loading = ref(false)
const error = ref('')
const auth = useAuthStore()
const router = useRouter()

async function onYearChange(value: number | null): Promise<void> {
    if (value !== null) {
        year.value = value
        await load()
    }
}

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
            await api.get<Competition[]>('/competitions', {
                params: competitionQuery(year.value),
            })
        ).data

        if (shouldLoadUsers(auth.isAuthenticated, users.value.length)) {
            users.value = (await api.get<User[]>('/users')).data
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
    <Toolbar class="page-toolbar">
        <template #start>
            <div>
                <h1 class="page-title">{{ t('spa.competitions.title') }}</h1>
                <p class="page-subtitle">
                    {{ t('spa.competitions.subtitle') }}
                </p>
            </div>
        </template>
        <template #end>
            <Button
                v-if="auth.isAuthenticated"
                icon="pi pi-plus"
                :label="t('spa.nav.create')"
                severity="success"
                @click="router.push('/app/competitions/create')"
            />
        </template>
    </Toolbar>

    <Card class="filter-card">
        <template #content>
            <div class="filter-panel">
                <label for="competition-year">{{
                    t('spa.competitions.year')
                }}</label>
                <InputNumber
                    id="competition-year"
                    v-model="year"
                    :min="2000"
                    :max="2100"
                    :use-grouping="false"
                    @update:model-value="onYearChange"
                />
            </div>
        </template>
    </Card>

    <Message v-if="loading" severity="info" :closable="false">{{
        t('spa.competitions.loading')
    }}</Message>
    <Message v-else-if="error" severity="error" :closable="false">{{
        error
    }}</Message>
    <Message
        v-else-if="!competitions.length"
        severity="secondary"
        :closable="false"
        >{{ t('spa.competitions.empty') }}</Message
    >
    <DataTable
        v-else
        :value="competitions"
        striped-rows
        class="competitions-table"
    >
        <Column field="name" :header="t('spa.competition.create.name')" />
        <Column :header="t('spa.competitions.dates')">
            <template #body="{ data }">{{
                formatDateRange(data.from, data.to)
            }}</template>
        </Column>
        <Column
            field="description"
            :header="t('spa.competitions.description')"
        />
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
