<script setup lang="ts">
import { ref, watch } from 'vue'
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

const props = defineProps<{ personId: string }>()
const auth = useAuthStore()
const person = ref<Person | null>(null)
const ranks = ref<RankOption[]>([])
const clubs = ref<ClubOption[]>([])
const users = ref<User[]>([])
const loading = ref(true)
const error = ref('')

async function load(): Promise<void> {
    try {
        const [loadedPerson, loadedRanks, loadedClubs, loadedUsers] =
            await Promise.all([
                getPerson(props.personId),
                getRanks(),
                getClubOptions(),
                auth.isAuthenticated ? getUsers() : Promise.resolve([]),
            ])
        person.value = loadedPerson
        ranks.value = loadedRanks
        clubs.value = loadedClubs
        users.value = loadedUsers
    } catch {
        error.value = t('spa.person_prompt.person_error')
    } finally {
        loading.value = false
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

watch(
    () => props.personId,
    () => {
        person.value = null
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
                        <th>{{ t('spa.person_prompt.birthday') }}</th>
                        <td>{{ person.birthday ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>{{ t('spa.person.rank') }}</th>
                        <td>{{ rankLabel(person.rankId) }}</td>
                    </tr>
                    <tr>
                        <th>{{ t('spa.person.club') }}</th>
                        <td>{{ clubLabel(person.clubId) }}</td>
                    </tr>
                    <tr>
                        <th>{{ t('spa.person.created') }}</th>
                        <td>
                            <ImpressionDetails
                                :impression="person.created"
                                :users="users"
                                :label="t('spa.person.created')"
                            />
                        </td>
                    </tr>
                    <tr>
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
        </template>
    </Card>
</template>
