<script setup lang="ts">
import { computed, ref } from 'vue'
import Popover from 'primevue/popover'
import { t } from '../i18n'
import { massIconClass } from '../pages/competitions/competitionModels'

const props = withDefaults(
    defineProps<{ mass: boolean; showLabel?: boolean }>(),
    { showLabel: false },
)

const popover = ref<{ toggle: (event: Event) => void } | null>(null)
const label = computed(() =>
    t(
        props.mass
            ? 'spa.competitions.mass_yes'
            : 'spa.competitions.mass_no',
    ),
)

function toggle(event: Event): void {
    popover.value?.toggle(event)
}
</script>

<template>
    <span class="mass-competition-indicator">
        <button
            type="button"
            class="mass-competition-indicator__button"
            :aria-label="label"
            @click="toggle"
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
        </button>
        <Popover ref="popover">
            <p class="mass-competition-indicator__popover">
                {{
                    t(
                        mass
                            ? 'spa.competitions.mass_hint_yes'
                            : 'spa.competitions.mass_hint_no',
                    )
                }}
            </p>
        </Popover>
    </span>
</template>
