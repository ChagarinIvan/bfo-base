// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import MassCompetitionIndicator from './MassCompetitionIndicator.vue'

describe('mass competition indicator', () => {
    it('explains that ranks do not apply to a mass competition', () => {
        const wrapper = mount(MassCompetitionIndicator, {
            props: { mass: true },
            global: { directives: { tooltip: {} } },
        })

        expect(wrapper.find('button').exists()).toBe(false)
        expect(wrapper.get('[role="img"]').attributes('aria-label')).toBe(
            'Масавыя спаборніцтвы',
        )
    })

    it('can render its label beside the icon for a competition card', () => {
        const wrapper = mount(MassCompetitionIndicator, {
            props: { mass: false, showLabel: true },
            global: { directives: { tooltip: {} } },
        })

        expect(wrapper.text()).toContain('Звычайныя спаборніцтвы')
    })
})
