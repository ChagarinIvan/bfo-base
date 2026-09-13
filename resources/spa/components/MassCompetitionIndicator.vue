<script setup lang="ts">
import { computed } from 'vue'
import { t } from '../i18n'
import { massIconClass } from '../pages/competitions/competitionModels'

const props = withDefaults(
    defineProps<{ mass: boolean; showLabel?: boolean }>(),
    { showLabel: false },
)

const label = computed(() =>
    t(
        props.mass
            ? 'spa.competitions.mass_yes'
            : 'spa.competitions.mass_no',
    ),
)
const hint = computed(() =>
    t(
        props.mass
            ? 'spa.competitions.mass_hint_yes'
            : 'spa.competitions.mass_hint_no',
    ),
)
</script>

<template>
    <span class="mass-competition-indicator">
        <span
            v-tooltip.top="hint"
            class="mass-competition-indicator__icon"
            :aria-label="label"
            role="img"
        >
            <i
                :class="[
                    massIconClass(mass),
                    'mass-icon',
                    mass ? 'mass-icon--active' : 'mass-icon--inactive',
                ]"
                aria-hidden="true"
            />
            <span v-if="showLabel">{{ label }}</span>
        </span>
    </span>
</template>
