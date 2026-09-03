<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'
import Button from 'primevue/button'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import Paginator, { type PageState } from 'primevue/paginator'
import { useRoute, useRouter } from 'vue-router'
import { getGroup, getGroups, mergeGroups } from '../../api/groups'
import type { Group, PaginationHeaders } from '../../api/types'
import ConfirmDeleteDialog from '../../components/actions/ConfirmDeleteDialog.vue'
import { t } from '../../i18n'
import { debounce, groupQuery, paginationFromHeaders } from './groupModels'
const route = useRoute()
const router = useRouter()
const source = ref<Group | null>(null)
const groups = ref<Group[]>([])
const name = ref('')
const target = ref<Group | null>(null)
const pending = ref(false)
const error = ref('')
const pagination = ref<PaginationHeaders>({
    currentPage: 1,
    perPage: 20,
    total: 0,
    lastPage: 1,
})
const filter = debounce(() => void load())
async function load(
    page = 1,
    perPage = pagination.value.perPage,
): Promise<void> {
    try {
        const r = await getGroups(
            groupQuery({
                name: name.value,
                excludeId: String(route.params.id),
                page,
                perPage,
            }),
        )
        groups.value = r.data
        pagination.value = paginationFromHeaders(r.headers)
    } catch {
        error.value = t('spa.group.merge.error')
    }
}
async function confirm(): Promise<void> {
    if (!target.value) return
    pending.value = true
    try {
        await mergeGroups(String(route.params.id), target.value.id)
        await router.push('/app/groups')
    } catch {
        error.value = t('spa.group.merge.error')
    } finally {
        pending.value = false
    }
}
onMounted(async () => {
    try {
        source.value = await getGroup(String(route.params.id))
        await load()
    } catch {
        error.value = t('spa.group.details.error')
    }
})
onBeforeUnmount(() => filter.cancel())
</script>
<template>
    <h1>
        {{ t('spa.group.merge') }}<span v-if="source">: {{ source.name }}</span>
    </h1>
    <InputText v-model="name" @update:model-value="filter" /><Message
        v-if="error"
        severity="error"
        :closable="false"
        >{{ error }}</Message
    ><DataTable :value="groups"
        ><Column field="name" :header="t('spa.groups.name')" /><Column
            ><template #body="{ data }"
                ><Button
                    :label="t('spa.group.merge')"
                    @click="target = data" /></template></Column></DataTable
    ><Paginator
        v-if="pagination.total"
        :first="(pagination.currentPage - 1) * pagination.perPage"
        :rows="pagination.perPage"
        :total-records="pagination.total"
        @page="(e: PageState) => load(e.page + 1, e.rows)"
    /><ConfirmDeleteDialog
        :visible="Boolean(target)"
        :title="t('spa.group.merge')"
        :confirmation="t('spa.group.merge.confirm')"
        :cancel-label="t('spa.common.cancel')"
        :action-label="t('spa.group.merge')"
        :pending="pending"
        @cancel="target = null"
        @confirm="confirm"
    />
</template>
