<script setup lang="ts">
import { onMounted, ref } from 'vue'
import Card from 'primevue/card'
import Message from 'primevue/message'
import { useRoute, useRouter } from 'vue-router'
import { getGroup, updateGroup } from '../../api/groups'
import type { Group, UpdateGroupRequest } from '../../api/types'
import { t } from '../../i18n'
import GroupForm from './GroupForm.vue'
const route = useRoute()
const router = useRouter()
const group = ref<Group | null>(null)
const error = ref('')
const pending = ref(false)
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
                @submit="submit" /></template
    ></Card>
</template>
