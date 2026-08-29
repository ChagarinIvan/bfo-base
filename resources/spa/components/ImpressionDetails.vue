<script setup lang="ts">
import { computed, ref } from 'vue'
import Popover from 'primevue/popover'
import { t } from '../i18n'
import type { Impression, User } from '../api/types'
import {
    formatImpressionDate,
    formatImpressionFullDate,
    impressionUserLabel,
} from './impressionModels'

const props = withDefaults(
    defineProps<{
        impression?: Impression
        users: User[]
        label?: string
    }>(),
    { impression: undefined, label: '' },
)

const popover = ref<{ toggle: (event: unknown) => void } | null>(null)

const userLabel = computed(() => {
    if (!props.impression) return ''

    return impressionUserLabel(
        props.impression,
        props.users,
        t('spa.competitions.unknown_user', { id: props.impression.by }),
    )
})

const shortDate = computed(() =>
    props.impression ? formatImpressionDate(props.impression.at) : '',
)

const fullDate = computed(() =>
    props.impression ? formatImpressionFullDate(props.impression.at) : '',
)

function toggle(event: unknown): void {
    popover.value?.toggle(event)
}
</script>

<template>
    <span v-if="impression" class="impression-details">
        <button
            type="button"
            class="impression-summary"
            :aria-label="`${label} — ${shortDate}, ${userLabel}`"
            @click="toggle"
        >
            <time class="impression-date" :datetime="impression.at">
                {{ shortDate }}
            </time>
            <span class="impression-user">{{ userLabel }}</span>
        </button>
        <Popover ref="popover">
            <div class="impression-popover">
                <strong v-if="label">{{ label }}</strong>
                <div class="impression-popover-row">
                    <span class="impression-popover-label">
                        {{ t('spa.impression.when') }}
                    </span>
                    <time :datetime="impression.at">{{ fullDate }}</time>
                </div>
                <div class="impression-popover-row">
                    <span class="impression-popover-label">
                        {{ t('spa.impression.who') }}
                    </span>
                    <span>{{ userLabel }}</span>
                </div>
                <div class="impression-popover-row">
                    <span class="impression-popover-label">
                        {{ t('spa.impression.user_id') }}
                    </span>
                    <span>{{ impression.by }}</span>
                </div>
            </div>
        </Popover>
    </span>
    <span v-else class="impression-empty">—</span>
</template>
