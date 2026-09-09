<script setup lang="ts">
import Button from 'primevue/button'
import Card from 'primevue/card'
import { useRouter } from 'vue-router'

const router = useRouter()
</script>

<template>
    <Card class="form-card">
        <template #title>Старонка не знойдзена</template>
        <template #content>
            <Button
                label="Да спаборніцтваў"
                @click="router.push('/app/competitions')"
            />
        </template>
    </Card>
</template>
