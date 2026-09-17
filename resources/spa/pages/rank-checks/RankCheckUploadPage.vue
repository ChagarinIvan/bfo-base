<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { createRankCheck } from '../../api/rankChecks'
import { t } from '../../i18n'

const router = useRouter()
const file = ref<File | null>(null)
const loading = ref(false)
const error = ref('')

async function submit(): Promise<void> {
    if (!file.value) return
    loading.value = true
    error.value = ''
    try {
        const check = await createRankCheck(file.value)
        await router.push(`/app/rank-checks/${check.id}`)
    } catch {
        error.value = t('spa.rank_check.failed')
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <section>
        <h1>{{ t('spa.rank_check.title') }}</h1>
        <form @submit.prevent="submit">
            <label for="rank-check-file">{{
                t('spa.rank_check.select_file')
            }}</label>
            <input
                id="rank-check-file"
                type="file"
                accept=".csv"
                @change="
                    file =
                        ($event.target as HTMLInputElement).files?.[0] ?? null
                "
            />
            <button type="submit" :disabled="!file || loading">
                {{ t('spa.rank_check.submit') }}
            </button>
            <p v-if="error" role="alert">{{ error }}</p>
        </form>
    </section>
</template>
