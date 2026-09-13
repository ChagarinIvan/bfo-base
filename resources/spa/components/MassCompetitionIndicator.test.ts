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

    it('renders only the icon while retaining its tooltip', () => {
        const wrapper = mount(MassCompetitionIndicator, {
            props: { mass: false },
            global: {
                directives: {
                    tooltip: {
                        mounted(element, binding) {
                            element.setAttribute(
                                'data-tooltip',
                                String(binding.value),
                            )
                        },
                    },
                },
            },
        })

        expect(wrapper.text()).toBe('')
        expect(wrapper.get('[role="img"]').attributes('data-tooltip')).toBe(
            'Звычайныя спаборніцтвы: разрады могуць прымяняцца.',
        )
    })
})
