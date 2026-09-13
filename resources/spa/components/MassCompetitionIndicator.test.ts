// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import MassCompetitionIndicator from './MassCompetitionIndicator.vue'

describe('mass competition indicator', () => {
    it('explains that ranks do not apply to a mass competition', () => {
        const wrapper = mount(MassCompetitionIndicator, {
            props: { mass: true },
            global: { stubs: { Popover: { template: '<div><slot /></div>' } } },
        })

        expect(wrapper.get('button').attributes('aria-label')).toBe(
            'Масавыя спаборніцтвы',
        )
        expect(wrapper.text()).toContain('разрады не прымяняюцца')
    })

    it('can render its label beside the icon for a competition card', () => {
        const wrapper = mount(MassCompetitionIndicator, {
            props: { mass: false, showLabel: true },
            global: { stubs: { Popover: { template: '<div><slot /></div>' } } },
        })

        expect(wrapper.text()).toContain('Звычайныя спаборніцтвы')
    })
})
