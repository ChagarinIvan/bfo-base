<script setup lang="ts">
import { computed, ref } from 'vue'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import ImpressionDetails from './ImpressionDetails.vue'
import PersonActionMenu from './actions/PersonActionMenu.vue'
import ConfirmDeleteDialog from './actions/ConfirmDeleteDialog.vue'
import ListingTable from './ListingTable.vue'
import type { ClubOption, Person, User } from '../api/types'
import { deletePerson } from '../api/persons'
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
const emit = defineEmits<{ deleted: [] }>()

const clubLabels = computed(() =>
    Object.fromEntries(props.clubs.map((club) => [club.id, club.name])),
)
const selectedPersonName = computed(() => {
    if (!selectedPerson.value) return ''

    return `${selectedPerson.value.lastname} ${selectedPerson.value.firstname}`
})
const columns = computed(() => [
    { key: 'lastname', label: t('spa.person.lastname'), defaultVisible: true },
    {
        key: 'firstname',
        label: t('spa.person.firstname'),
        defaultVisible: true,
    },
    ...(!props.hideClub
        ? [{ key: 'club', label: t('spa.person.club'), defaultVisible: true }]
        : []),
    {
        key: 'birthYear',
        label: t('spa.person.birth_year'),
        defaultVisible: true,
    },
    { key: 'rank', label: t('spa.person.rank'), defaultVisible: true },
    ...(props.authenticated
        ? [
              {
                  key: 'created',
                  label: t('spa.person.created'),
                  defaultVisible: true,
              },
              {
                  key: 'updated',
                  label: t('spa.person.updated'),
                  defaultVisible: true,
              },
              {
                  key: 'actions',
                  label: t('spa.person.actions'),
                  defaultVisible: true,
              },
          ]
        : []),
])

async function deleteSelectedPerson(): Promise<void> {
    if (!selectedPerson.value) return
    await deletePerson(selectedPerson.value.id)
    selectedPerson.value = null
    emit('deleted')
}

function birthYear(birthday: string | null): string {
    return birthday?.slice(0, 4) ?? '—'
}
</script>

<template>
    <ListingTable
        table-id="persons"
        :columns="columns"
        :authenticated="authenticated"
    >
        <template v-if="$slots.filters" #filters>
            <slot name="filters" />
        </template>
        <template #default="{ isVisible }">
            <DataTable
                v-if="persons.length"
                :value="persons"
                striped-rows
                class="persons-table"
            >
                <Column
                    v-if="isVisible('lastname')"
                    field="lastname"
                    :header="t('spa.person.lastname')"
                >
                    <template #body="{ data }">
                        <RouterLink :to="`/app/persons/${data.id}`">
                            {{ data.lastname }}
                        </RouterLink>
                    </template>
                </Column>
                <Column
                    v-if="isVisible('firstname')"
                    field="firstname"
                    :header="t('spa.person.firstname')"
                >
                    <template #body="{ data }">
                        <RouterLink :to="`/app/persons/${data.id}`">
                            {{ data.firstname }}
                        </RouterLink>
                    </template>
                </Column>
                <Column
                    v-if="!hideClub && isVisible('club')"
                    :header="t('spa.person.club')"
                >
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
                <Column
                    v-if="isVisible('birthYear')"
                    :header="t('spa.person.birth_year')"
                >
                    <template #body="{ data }">
                        {{ birthYear(data.birthday) }}
                    </template>
                </Column>
                <Column v-if="isVisible('rank')" :header="t('spa.person.rank')">
                    <template #body="{ data }">
                        {{ rankLabels[data.rankId] ?? data.rankId }}
                    </template>
                </Column>
                <Column
                    v-if="authenticated && isVisible('created')"
                    :header="t('spa.person.created')"
                >
                    <template #body="{ data }">
                        <ImpressionDetails
                            :impression="data.created"
                            :users="users"
                            :label="t('spa.person.created')"
                        />
                    </template>
                </Column>
                <Column
                    v-if="authenticated && isVisible('updated')"
                    :header="t('spa.person.updated')"
                >
                    <template #body="{ data }">
                        <ImpressionDetails
                            :impression="data.updated"
                            :users="users"
                            :label="t('spa.person.updated')"
                        />
                    </template>
                </Column>
                <Column
                    v-if="authenticated && isVisible('actions')"
                    :header="t('spa.person.actions')"
                >
                    <template #body="{ data }">
                        <PersonActionMenu
                            :person-id="data.id"
                            @delete="selectedPerson = data"
                        />
                    </template>
                </Column>
            </DataTable>
        </template>
    </ListingTable>
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
