<script setup lang="ts">
import { reactive, watch } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import type { ClubOption, PersonFormRequest } from '../../api/types'
import ClubSelect from '../../components/ClubSelect.vue'
import DateFilter from '../../components/DateFilter.vue'
import { t } from '../../i18n'

const props = withDefaults(
    defineProps<{
        initialValue?: Partial<PersonFormRequest>
        clubs: ClubOption[]
        errors?: Record<string, string>
        pending?: boolean
    }>(),
    { initialValue: () => ({}), errors: () => ({}), pending: false },
)
const emit = defineEmits<{ submit: [value: PersonFormRequest] }>()
const form = reactive<PersonFormRequest>({
    lastname: '',
    firstname: '',
    birthday: null,
    clubId: null,
    citizenship: 'belarus',
})
watch(
    () => props.initialValue,
    (value) =>
        Object.assign(form, {
            lastname: value.lastname ?? '',
            firstname: value.firstname ?? '',
            birthday: value.birthday ?? null,
            clubId: value.clubId ?? null,
            citizenship: value.citizenship ?? 'belarus',
        }),
    { immediate: true },
)
</script>
<template>
    <form
        class="spa-form"
        @submit.prevent="
            emit('submit', {
                ...form,
                lastname: form.lastname.trim(),
                firstname: form.firstname.trim(),
            })
        "
    >
        <div class="form-field">
            <label for="person-lastname">{{ t('spa.person.lastname') }}</label
            ><InputText
                id="person-lastname"
                v-model="form.lastname"
                required
                :invalid="Boolean(errors.lastname)"
            /><small v-if="errors.lastname" class="field-error">{{
                errors.lastname
            }}</small>
        </div>
        <div class="form-field">
            <label for="person-firstname">{{ t('spa.person.firstname') }}</label
            ><InputText
                id="person-firstname"
                v-model="form.firstname"
                required
                :invalid="Boolean(errors.firstname)"
            /><small v-if="errors.firstname" class="field-error">{{
                errors.firstname
            }}</small>
        </div>
        <DateFilter
            class="form-field"
            input-id="person-birthday"
            :model-value="form.birthday ?? ''"
            :label="t('app.common.birthday')"
            :error="errors.birthday"
            @update:model-value="form.birthday = $event || null"
        />
        <div class="form-field">
            <ClubSelect
                v-model="form.clubId"
                input-id="person-club"
                :clubs="clubs"
                :label="t('spa.person.club')"
                clearable
            /><small v-if="errors.clubId" class="field-error">{{
                errors.clubId
            }}</small>
        </div>
        <div class="form-field">
            <label for="person-citizenship">{{
                t('app.common.citizenship')
            }}</label
            ><Select
                id="person-citizenship"
                v-model="form.citizenship"
                :options="[
                    {
                        label: t('app.common.citizenship.belarus'),
                        value: 'belarus',
                    },
                    {
                        label: t('app.common.citizenship.other'),
                        value: 'other',
                    },
                ]"
                option-label="label"
                option-value="value"
            /><small v-if="errors.citizenship" class="field-error">{{
                errors.citizenship
            }}</small>
        </div>
        <Button
            type="submit"
            :label="t('app.common.save')"
            severity="success"
            :loading="pending"
        />
    </form>
</template>
