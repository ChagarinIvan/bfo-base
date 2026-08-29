<script setup lang="ts">
import Toast from 'primevue/toast'
import AppLayout from './components/AppLayout.vue'
</script>

<template>
  <AppLayout>
    <RouterView />
  </AppLayout>
  <Toast />
</template>
