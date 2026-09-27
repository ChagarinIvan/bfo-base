<script setup lang="ts">
import { onBeforeUnmount, provide, ref, watch } from 'vue'
import type { AxiosError } from 'axios'
import Card from 'primevue/card'
import Message from 'primevue/message'
import { useRoute, useRouter } from 'vue-router'
import { deleteCup as disableCup, getCup } from '../../api/cups'
import type { Cup, User } from '../../api/types'
import { getUsers } from '../../api/users'
import CupInfoNavigation from '../../components/CupInfoNavigation.vue'
import CupTypeIcon from '../../components/CupTypeIcon.vue'
import ImpressionDetails from '../../components/ImpressionDetails.vue'
import ConfirmDeleteDialog from '../../components/actions/ConfirmDeleteDialog.vue'
import { useAuthStore } from '../../stores/auth'
import { cupGroupBadgeClass } from './cupModels'
import { cupContextKey } from './cupContext'
import { t } from '../../i18n'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const cup = ref<Cup | null>(null)
const users = ref<User[]>([])
const deleteCupVisible = ref(false)
const loading = ref(true)
const error = ref('')
const controller = ref<AbortController | null>(null)
provide(cupContextKey, cup)

function isNotFound(exception: unknown): boolean {
    return (
        typeof exception === 'object' &&
        exception !== null &&
        'isAxiosError' in exception &&
        exception.isAxiosError === true &&
        (exception as AxiosError).response?.status === 404
    )
}

async function load(cupId: string): Promise<void> {
    controller.value?.abort()
    const current = new AbortController()
    controller.value = current
    cup.value = null
    loading.value = true
    error.value = ''
    try {
        cup.value = await getCup(cupId, current.signal)
        users.value = auth.isAuthenticated
            ? await getUsers().catch(() => [])
            : []
    } catch (exception) {
        cup.value = null
        if (isNotFound(exception)) {
            await router.replace({ name: 'not-found' })
            return
        }
        error.value = t('spa.cups.error')
    } finally {
        if (controller.value === current) loading.value = false
    }
}

async function deleteCup(): Promise<void> {
    if (!cup.value) return
    try {
        await disableCup(cup.value.id)
        await router.push('/app/cups')
    } catch {
        error.value = t('spa.cups.error')
    }
}

watch(
    () => String(route.params.cupId),
    (id) => void load(id),
    { immediate: true },
)
watch(
    () => auth.isAuthenticated,
    (authenticated) => {
        if (!authenticated || !cup.value) {
            users.value = []
            return
        }

        void getUsers()
            .then((result) => {
                users.value = result
            })
            .catch(() => {
                users.value = []
            })
    },
)
onBeforeUnmount(() => controller.value?.abort())
</script>

<template>
    <Message v-if="loading && !cup" severity="info" :closable="false">
        {{ t('spa.cups.loading') }}
    </Message>
    <Message v-else-if="error" severity="error" :closable="false">
        {{ error }}
    </Message>
    <template v-if="cup">
        <Card class="competition-details-card">
            <template #title>{{ cup.name }}</template>
            <template #content>
                <table class="competition-details-info">
                    <tbody>
                        <tr>
                            <th>{{ t('spa.cup.form.year') }}</th>
                            <td>{{ cup.year }}</td>
                        </tr>
                        <tr>
                            <th>{{ t('spa.cup.type') }}</th>
                            <td>
                                <CupTypeIcon :type="cup.type" />
                                {{ t(`app.cup.type.${cup.type}` as never) }}
                            </td>
                        </tr>
                        <tr>
                            <th>{{ t('spa.cups.events_count') }}</th>
                            <td>{{ cup.eventsCount }}</td>
                        </tr>
                        <tr>
                            <th>{{ t('spa.cups.groups') }}</th>
                            <td>
                                <RouterLink
                                    v-for="group in cup.groups"
                                    :key="group.id"
                                    :to="`/app/cups/${cup.id}/table/${group.id}`"
                                    :class="[
                                        'cup-group-badge',
                                        cupGroupBadgeClass(group),
                                    ]"
                                >
                                    {{ group.name }}
                                </RouterLink>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ t('spa.cups.visibility') }}</th>
                            <td>
                                <span class="mass-competition-indicator__icon">
                                    <i
                                        :class="[
                                            cup.visible
                                                ? 'pi pi-check-square'
                                                : 'pi pi-times-circle',
                                            'mass-icon',
                                            cup.visible
                                                ? 'mass-icon--active'
                                                : 'mass-icon--inactive',
                                        ]"
                                        :aria-label="
                                            cup.visible
                                                ? t('spa.cups.visible_yes')
                                                : t('spa.cups.visible_no')
                                        "
                                        :title="
                                            cup.visible
                                                ? t('spa.cups.visible_yes')
                                                : t('spa.cups.visible_no')
                                        "
                                        role="img"
                                    />
                                </span>
                            </td>
                        </tr>
                        <tr v-if="auth.isAuthenticated">
                            <th>{{ t('spa.cups.created') }}</th>
                            <td>
                                <ImpressionDetails
                                    :impression="cup.created"
                                    :users="users"
                                    :label="t('spa.cups.created')"
                                />
                            </td>
                        </tr>
                        <tr v-if="auth.isAuthenticated">
                            <th>{{ t('spa.cups.updated') }}</th>
                            <td>
                                <ImpressionDetails
                                    :impression="cup.updated"
                                    :users="users"
                                    :label="t('spa.cups.updated')"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <CupInfoNavigation
                    :cup-id="cup.id"
                    :first-group-id="cup.groups[0]?.id"
                    @delete-cup="deleteCupVisible = true"
                />
            </template>
        </Card>
        <RouterView />
        <ConfirmDeleteDialog
            v-if="auth.isAuthenticated"
            :visible="deleteCupVisible"
            :title="t('spa.cups.delete.title')"
            :confirmation="t('spa.cups.delete.confirm', { name: cup.name })"
            :cancel-label="t('spa.cups.delete.cancel')"
            :action-label="t('spa.cups.delete.action')"
            @cancel="deleteCupVisible = false"
            @confirm="deleteCup"
        />
    </template>
</template>
