// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import CupTypeIcon from './CupTypeIcon.vue'

describe('cup type icon', () => {
    it('renders an accessible icon and optional label', () => {
        const wrapper = mount(CupTypeIcon, {
            props: { type: 'bike', showLabel: true },
        })

        expect(wrapper.find('i').classes()).toEqual(
            expect.arrayContaining(['fas', 'fa-bicycle']),
        )
        expect(wrapper.find('i').attributes('aria-label')).toBeTruthy()
        expect(wrapper.find('.cup-type-icon__label').exists()).toBe(true)
    })

    it('uses the neutral fallback for unknown values', () => {
        const wrapper = mount(CupTypeIcon, { props: { type: 'future_type' } })

        expect(wrapper.find('i').classes()).toEqual(
            expect.arrayContaining(['fa-question-circle']),
        )
    })
})
