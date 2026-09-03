<script setup lang="ts">
import { onMounted, ref } from 'vue'
import Card from 'primevue/card'
import Message from 'primevue/message'
import { useRoute, useRouter } from 'vue-router'
import { getGroup, updateGroup, deleteGroup } from '../../api/groups'
import type { Group, UpdateGroupRequest } from '../../api/types'
import ConfirmDeleteDialog from '../../components/actions/ConfirmDeleteDialog.vue'
import { t } from '../../i18n'
import GroupForm from './GroupForm.vue'
const route = useRoute()
const router = useRouter()
const group = ref<Group | null>(null)
const error = ref('')
const pending = ref(false)
const deleting = ref(route.query.delete === '1')
async function load(): Promise<void> {
    try {
        group.value = await getGroup(String(route.params.id))
    } catch {
        error.value = t('spa.group.details.error')
    }
}
async function submit(value: UpdateGroupRequest): Promise<void> {
    pending.value = true
    try {
        await router.push(
            `/app/groups/${(await updateGroup(String(route.params.id), value)).id}`,
        )
    } catch {
        error.value = t('spa.group.edit.error')
    } finally {
        pending.value = false
    }
}
async function remove(): Promise<void> {
    pending.value = true
    try {
        await deleteGroup(String(route.params.id))
        await router.push('/app/groups')
    } catch {
        error.value = t('spa.group.delete.error')
    } finally {
        pending.value = false
    }
}
onMounted(() => void load())
</script>
<template>
    <Message v-if="error && !group" severity="error" :closable="false">{{
        error
    }}</Message
    ><Card v-else-if="group"
        ><template #title>{{ t('spa.group.edit') }}</template
        ><template #content
            ><GroupForm
                :initial-value="group"
                :pending="pending"
                :error="error"
                @submit="submit"
            /><button
                type="button"
                class="p-button p-button-danger"
                @click="deleting = true"
            >
                {{ t('spa.group.delete') }}
            </button></template
        ></Card
    ><ConfirmDeleteDialog
        :visible="deleting"
        :title="t('spa.group.delete')"
        :confirmation="t('spa.group.delete.confirm')"
        :cancel-label="t('spa.common.cancel')"
        :action-label="t('spa.group.delete')"
        :pending="pending"
        @cancel="deleting = false"
        @confirm="remove"
    />
</template>
