<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import Card from 'primevue/card'
import Message from 'primevue/message'
import { getClubOptions } from '../api/clubs'
import { getPerson } from '../api/persons'
import { getRanks, type RankOption } from '../api/ranks'
import { getUsers } from '../api/users'
import type { ClubOption, Person, User } from '../api/types'
import ImpressionDetails from './ImpressionDetails.vue'
import { t } from '../i18n'
import { useAuthStore } from '../stores/auth'

const props = defineProps<{ personId: string; refreshKey?: string }>()
const emit = defineEmits<{
    personLoaded: [person: Person | null]
}>()
const auth = useAuthStore()
const router = useRouter()
const person = ref<Person | null>(null)
const ranks = ref<RankOption[]>([])
const clubs = ref<ClubOption[]>([])
const users = ref<User[]>([])
const loading = ref(true)
const error = ref('')
let latestRequest = 0

async function load(): Promise<void> {
    const requestId = ++latestRequest
    const personId = props.personId

    try {
        const [loadedPerson, loadedRanks, loadedClubs, loadedUsers] =
            await Promise.all([
                getPerson(personId, props.refreshKey),
                getRanks(),
                getClubOptions(),
                auth.isAuthenticated ? getUsers() : Promise.resolve([]),
            ])
        if (requestId !== latestRequest) return
        person.value = loadedPerson
        emit('personLoaded', loadedPerson)
        ranks.value = loadedRanks
        clubs.value = loadedClubs
        users.value = loadedUsers
    } catch (exception: unknown) {
        if (requestId !== latestRequest) return
        person.value = null
        emit('personLoaded', null)

        const status = (exception as { response?: { status?: number } })
            .response?.status
        if (status === 404) {
            await router.replace({ name: 'not-found' })
            return
        }

        error.value = t('spa.person_prompt.person_error')
    } finally {
        if (requestId === latestRequest) loading.value = false
    }
}

function rankLabel(rankId: number): string {
    return (
        ranks.value.find((rank) => rank.id === rankId)?.label ?? String(rankId)
    )
}

function clubLabel(clubId: string | null): string {
    return clubs.value.find((club) => club.id === clubId)?.name ?? '—'
}

function birthYear(birthday: string | null): string {
    return birthday?.slice(0, 4) ?? '—'
}

watch(
    [() => props.personId, () => props.refreshKey],
    () => {
        person.value = null
        emit('personLoaded', null)
        loading.value = true
        error.value = ''
        void load()
    },
    { immediate: true },
)
</script>

<template>
    <Message
        v-if="loading || error"
        :severity="error ? 'error' : 'info'"
        :closable="false"
    >
        {{ error || t('spa.person_prompt.person_loading') }}
    </Message>
    <Card v-else-if="person" class="club-details-card">
        <template #title>{{ t('spa.person_prompt.person_info') }}</template>
        <template #content>
            <table class="club-details-info">
                <tbody>
                    <tr>
                        <th>{{ t('spa.person.lastname') }}</th>
                        <td>{{ person.lastname }}</td>
                    </tr>
                    <tr>
                        <th>{{ t('spa.person.firstname') }}</th>
                        <td>{{ person.firstname }}</td>
                    </tr>
                    <tr>
                        <th>{{ t('spa.person.birth_year') }}</th>
                        <td>{{ birthYear(person.birthday) }}</td>
                    </tr>
                    <tr>
                        <th>{{ t('spa.person.rank') }}</th>
                        <td>{{ rankLabel(person.rankId) }}</td>
                    </tr>
                    <tr>
                        <th>{{ t('spa.person.club') }}</th>
                        <td>
                            <RouterLink
                                v-if="person.clubId"
                                :to="`/app/clubs/${person.clubId}`"
                                >{{ clubLabel(person.clubId) }}</RouterLink
                            >
                            <span v-else>—</span>
                        </td>
                    </tr>
                    <tr v-if="auth.isAuthenticated">
                        <th>{{ t('spa.person.created') }}</th>
                        <td>
                            <ImpressionDetails
                                :impression="person.created"
                                :users="users"
                                :label="t('spa.person.created')"
                            />
                        </td>
                    </tr>
                    <tr v-if="auth.isAuthenticated">
                        <th>{{ t('spa.person.updated') }}</th>
                        <td>
                            <ImpressionDetails
                                :impression="person.updated"
                                :users="users"
                                :label="t('spa.person.updated')"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
            <slot name="actions" />
        </template>
    </Card>
</template>
