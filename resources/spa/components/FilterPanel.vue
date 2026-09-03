<script setup lang="ts">
import Card from 'primevue/card'
</script>

<template>
    <Card class="filter-card">
        <template #content>
            <div class="filter-panel">
                <slot />
            </div>
        </template>
    </Card>
</template>
