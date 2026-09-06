<script setup lang="ts">
import { provide, ref } from 'vue'
import { useRoute } from 'vue-router'
import type { Person } from '../../api/types'
import PersonInfoNavigation from '../../components/PersonInfoNavigation.vue'
import PersonPromptPersonInfo from '../../components/PersonPromptPersonInfo.vue'
import { personContextKey } from './personContext'

const route = useRoute()
const person = ref<Person | null>(null)

provide(personContextKey, person)
</script>

<template>
    <PersonPromptPersonInfo
        :person-id="String(route.params.personId)"
        @person-loaded="person = $event"
    >
        <template #actions>
            <PersonInfoNavigation :person-id="String(route.params.personId)" />
        </template>
    </PersonPromptPersonInfo>
    <RouterView />
</template>
