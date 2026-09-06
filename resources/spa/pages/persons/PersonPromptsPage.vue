<script setup lang="ts">
import { ref, watch } from 'vue'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import Message from 'primevue/message'
import Paginator, { type PageState } from 'primevue/paginator'
import Button from 'primevue/button'
import { useRoute, useRouter } from 'vue-router'
import { deletePersonPrompt, getPersonPrompts } from '../../api/personPrompts'
import type { PersonPrompt, PaginationHeaders } from '../../api/types'
import ConfirmDeleteDialog from '../../components/actions/ConfirmDeleteDialog.vue'
import PersonPromptActionMenu from '../../components/actions/PersonPromptActionMenu.vue'
import { t } from '../../i18n'
import { useAuthStore } from '../../stores/auth'
import { paginationFromHeaders } from '../listingModels'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const prompts = ref<PersonPrompt[]>([])
const loading = ref(true)
const error = ref('')
const selected = ref<PersonPrompt | null>(null)
const pending = ref(false)
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    total: 0,
    lastPage: 1,
})
let latestRequest = 0

async function load(
    page = 1,
    perPage = pagination.value.perPage,
): Promise<void> {
    const requestId = ++latestRequest
    loading.value = true
    error.value = ''
    try {
        const response = await getPersonPrompts({
            personId: String(route.params.personId),
            page,
            perPage,
        })
        if (requestId !== latestRequest) return
        prompts.value = response.data
        pagination.value = paginationFromHeaders(response.headers)
    } catch {
        if (requestId === latestRequest)
            error.value = t('spa.person_prompt.error')
    } finally {
        if (requestId === latestRequest) loading.value = false
    }
}

async function remove(): Promise<void> {
    if (!selected.value) return
    pending.value = true
    try {
        await deletePersonPrompt(selected.value.id)
        selected.value = null
        const page =
            prompts.value.length === 1 && pagination.value.currentPage > 1
                ? pagination.value.currentPage - 1
                : pagination.value.currentPage
        await load(page)
    } catch {
        error.value = t('spa.person_prompt.delete_error')
    } finally {
        pending.value = false
    }
}

watch(
    () => String(route.params.personId),
    () => void load(),
    { immediate: true },
)
</script>

<template>
    <div class="page-toolbar">
        <h1 class="page-title">{{ t('spa.person_prompt.title') }}</h1>
        <Button
            v-if="auth.isAuthenticated"
            :label="t('spa.person_prompt.create')"
            icon="pi pi-plus"
            severity="success"
            @click="
                router.push(
                    `/app/persons/${route.params.personId}/prompts/create`,
                )
            "
        />
    </div>
    <Message v-if="loading" severity="info" :closable="false">{{
        t('spa.person_prompt.loading')
    }}</Message>
    <Message v-else-if="error" severity="error" :closable="false">{{
        error
    }}</Message>
    <Message
        v-else-if="!prompts.length"
        severity="secondary"
        :closable="false"
        >{{ t('spa.person_prompt.empty') }}</Message
    >
    <DataTable v-else :value="prompts" striped-rows>
        <Column field="prompt" :header="t('spa.person_prompt.prompt')" />
        <Column field="metaphone" :header="t('spa.person_prompt.metaphone')" />
        <Column
            v-if="auth.isAuthenticated"
            :header="t('spa.person_prompt.actions')"
            ><template #body="{ data }"
                ><PersonPromptActionMenu
                    :person-id="data.personId"
                    :prompt-id="data.id"
                    @delete="selected = data" /></template
        ></Column>
    </DataTable>
    <Paginator
        v-if="pagination.total > 0"
        :first="(pagination.currentPage - 1) * pagination.perPage"
        :rows="pagination.perPage"
        :total-records="pagination.total"
        :rows-per-page-options="[10, 20, 50]"
        @page="(event: PageState) => void load(event.page + 1, event.rows)"
    />
    <ConfirmDeleteDialog
        v-if="selected"
        :visible="Boolean(selected)"
        :title="t('spa.person_prompt.delete_title')"
        :confirmation="
            t('spa.person_prompt.delete_confirm', { prompt: selected.prompt })
        "
        :cancel-label="t('spa.person_prompt.cancel')"
        :action-label="t('spa.person_prompt.delete')"
        :pending="pending"
        @cancel="selected = null"
        @confirm="remove"
    />
</template>
