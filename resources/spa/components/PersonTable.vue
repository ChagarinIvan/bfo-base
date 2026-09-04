<script setup lang="ts">
import { computed, ref } from 'vue'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import ImpressionDetails from './ImpressionDetails.vue'
import PersonActionMenu from './actions/PersonActionMenu.vue'
import ConfirmDeleteDialog from './actions/ConfirmDeleteDialog.vue'
import type { ClubOption, Person, User } from '../api/types'
import { t } from '../i18n'

const props = withDefaults(
    defineProps<{
        persons: Person[]
        users: User[]
        clubs?: ClubOption[]
        authenticated?: boolean
        rankLabels?: Record<number, string>
        hideClub?: boolean
    }>(),
    {
        clubs: () => [],
        authenticated: false,
        rankLabels: () => ({}),
        hideClub: false,
    },
)

const selectedPerson = ref<Person | null>(null)

const clubLabels = computed(() =>
    Object.fromEntries(props.clubs.map((club) => [club.id, club.name])),
)
const selectedPersonName = computed(() => {
    if (!selectedPerson.value) return ''

    return `${selectedPerson.value.lastname} ${selectedPerson.value.firstname}`
})

function deleteSelectedPerson(): void {
    if (!selectedPerson.value) return

    globalThis.location.assign(`/persons/${selectedPerson.value.id}/delete`)
}

function birthYear(birthday: string | null): string {
    return birthday?.slice(0, 4) ?? '—'
}
</script>

<template>
    <DataTable :value="persons" striped-rows class="persons-table">
        <Column field="lastname" :header="t('spa.person.lastname')">
            <template #body="{ data }">
                <a :href="`/persons/${data.id}/show`">{{ data.lastname }}</a>
            </template>
        </Column>
        <Column field="firstname" :header="t('spa.person.firstname')">
            <template #body="{ data }">
                <a :href="`/persons/${data.id}/show`">{{ data.firstname }}</a>
            </template>
        </Column>
        <Column v-if="!hideClub" :header="t('spa.person.club')">
            <template #body="{ data }">
                <RouterLink
                    v-if="data.clubId && clubLabels[data.clubId]"
                    :to="`/app/clubs/${data.clubId}`"
                >
                    {{ clubLabels[data.clubId] }}
                </RouterLink>
                <span v-else>—</span>
            </template>
        </Column>
        <Column :header="t('spa.person.birth_year')">
            <template #body="{ data }">
                {{ birthYear(data.birthday) }}
            </template>
        </Column>
        <Column :header="t('spa.person.rank')">
            <template #body="{ data }">
                {{ rankLabels[data.rankId] ?? data.rankId }}
            </template>
        </Column>
        <Column v-if="authenticated" :header="t('spa.person.created')">
            <template #body="{ data }">
                <ImpressionDetails
                    :impression="data.created"
                    :users="users"
                    :label="t('spa.person.created')"
                />
            </template>
        </Column>
        <Column v-if="authenticated" :header="t('spa.person.updated')">
            <template #body="{ data }">
                <ImpressionDetails
                    :impression="data.updated"
                    :users="users"
                    :label="t('spa.person.updated')"
                />
            </template>
        </Column>
        <Column v-if="authenticated" :header="t('spa.person.actions')">
            <template #body="{ data }">
                <PersonActionMenu
                    :person-id="data.id"
                    @delete="selectedPerson = data"
                />
            </template>
        </Column>
    </DataTable>
    <ConfirmDeleteDialog
        v-if="authenticated && selectedPerson"
        :visible="Boolean(selectedPerson)"
        :title="t('spa.person.delete.title')"
        :confirmation="
            t('spa.person.delete.confirm', { name: selectedPersonName })
        "
        :cancel-label="t('spa.person.delete.cancel')"
        :action-label="t('spa.person.delete')"
        @cancel="selectedPerson = null"
        @confirm="deleteSelectedPerson"
    />
</template>
