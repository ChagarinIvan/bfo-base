<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from 'vue'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Message from 'primevue/message'
import Select from 'primevue/select'
import { addManualPersonRank } from '../api/personRankHistory'
import type { RankOption } from '../api/ranks'
import { t } from '../i18n'

const props = defineProps<{ personId: string; ranks: RankOption[] }>()
const emit = defineEmits<{ cancel: []; saved: [personId: string] }>()
const availableRanks = computed(() =>
    props.ranks.filter((rank) => rank.id !== 0),
)
const rankId = ref<number | null>(null)
const achievedOn = ref('')
const activatedOn = ref('')
const saving = ref(false)
const error = ref('')
let live = true
onBeforeUnmount(() => {
    live = false
})

function cancel(): void {
    if (!saving.value) emit('cancel')
}
async function save(): Promise<void> {
    if (saving.value) return
    error.value = ''
    if (
        !rankId.value ||
        !achievedOn.value ||
        !activatedOn.value ||
        activatedOn.value < achievedOn.value
    ) {
        error.value = t('spa.person_rank.manual_invalid')
        return
    }
    const target = props.personId
    saving.value = true
    try {
        await addManualPersonRank(target, {
            rankId: rankId.value,
            achievedOn: achievedOn.value,
            activatedOn: activatedOn.value,
        })
        if (live && target === props.personId) emit('saved', target)
    } catch {
        if (live && target === props.personId)
            error.value = t('spa.person_rank.manual_save_error')
    } finally {
        saving.value = false
    }
}
</script>

<template>
    <Dialog
        :visible="true"
        modal
        :header="t('spa.person_rank.manual_add')"
        :closable="!saving"
        :close-on-escape="!saving"
        @update:visible="cancel"
    >
        <form @submit.prevent="save">
            <div class="form-field">
                <label for="manual-rank-id">{{
                    t('spa.person_rank.rank')
                }}</label>
                <Select
                    v-model="rankId"
                    input-id="manual-rank-id"
                    :options="availableRanks"
                    option-label="label"
                    option-value="id"
                    :disabled="saving"
                />
            </div>
            <div class="form-field">
                <label for="manual-rank-achieved">{{
                    t('spa.person_rank.completed')
                }}</label>
                <input
                    id="manual-rank-achieved"
                    v-model="achievedOn"
                    class="form-control"
                    type="date"
                    required
                    :disabled="saving"
                />
            </div>
            <div class="form-field">
                <label for="manual-rank-activated">{{
                    t('spa.person_rank.activated')
                }}</label>
                <input
                    id="manual-rank-activated"
                    v-model="activatedOn"
                    class="form-control"
                    type="date"
                    required
                    :min="achievedOn"
                    :disabled="saving"
                />
            </div>
            <Message v-if="error" severity="error" :closable="false">{{
                error
            }}</Message>
            <div class="details-actions">
                <Button
                    :label="t('spa.person_rank.cancel')"
                    severity="secondary"
                    :disabled="saving"
                    @click="cancel"
                />
                <Button
                    type="submit"
                    :label="t('spa.person_rank.save')"
                    severity="success"
                    :loading="saving"
                />
            </div>
        </form>
    </Dialog>
</template>
