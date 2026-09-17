<script setup lang="ts">
import { ref } from 'vue'
import Button from 'primevue/button'
import Card from 'primevue/card'
import FileUpload from 'primevue/fileupload'
import Message from 'primevue/message'
import { useRouter } from 'vue-router'
import { createRankCheck } from '../../api/rankChecks'
import { t } from '../../i18n'

const router = useRouter()
const file = ref<File | null>(null)
const loading = ref(false)
const error = ref('')

function selectFile(event: { files: File[] }): void {
    file.value = event.files[0] ?? null
}

async function submit(): Promise<void> {
    if (!file.value) return
    loading.value = true
    error.value = ''
    try {
        await createRankCheck(file.value)
        await router.push('/app/rank-checks')
    } catch {
        error.value = t('spa.rank_check.failed')
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <Card class="form-card">
        <template #title>{{ t('spa.rank_check.title') }}</template>
        <template #content>
            <form class="spa-form" @submit.prevent="submit">
                <div class="form-field">
                    <label>{{ t('spa.rank_check.select_file') }}</label>
                    <FileUpload
                        mode="basic"
                        name="list"
                        accept=".csv"
                        :choose-label="t('spa.rank_check.select_file')"
                        :auto="false"
                        @select="selectFile"
                    />
                    <small v-if="file">{{ file.name }}</small>
                </div>
                <Button
                    type="submit"
                    :label="t('spa.rank_check.submit')"
                    severity="success"
                    :loading="loading"
                    :disabled="!file"
                />
                <Message
                    v-if="error"
                    severity="error"
                    :closable="false"
                    role="alert"
                    >{{ error }}</Message
                >
            </form>
        </template>
    </Card>
</template>
