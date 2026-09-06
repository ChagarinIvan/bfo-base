<script setup lang="ts">
import { useRoute } from 'vue-router'
import PersonInfoNavigation from '../../components/PersonInfoNavigation.vue'
import PersonPromptPersonInfo from '../../components/PersonPromptPersonInfo.vue'

const route = useRoute()
</script>

<template>
    <PersonPromptPersonInfo :person-id="String(route.params.personId)">
        <template #actions>
            <PersonInfoNavigation :person-id="String(route.params.personId)" />
        </template>
    </PersonPromptPersonInfo>
    <RouterView />
</template>
