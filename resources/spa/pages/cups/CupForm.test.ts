// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
import { describe, expect, it, vi } from 'vitest'
import CupForm from './CupForm.vue'
import { CUP_TYPES } from '../../components/cupTypeModels'

vi.mock('../../api/years', () => ({
    getYears: vi.fn().mockResolvedValue([2026, 2025]),
}))

describe('cup form', () => {
    it('emits the complete prefilled payload', async () => {
        const wrapper = mount(CupForm, {
            props: {
                initialValue: {
                    name: 'Existing cup',
                    eventsCount: '4',
                    year: 2025,
                    type: 'master',
                    visible: false,
                },
                submitLabel: 'Захаваць',
            },
            global: { plugins: [PrimeVue] },
        })

        expect(
            wrapper.findComponent({ name: 'Select' }).props('options'),
        ).toHaveLength(CUP_TYPES.length)
        await wrapper.find('form').trigger('submit')

        expect(wrapper.emitted('submit')).toEqual([
            [
                {
                    name: 'Existing cup',
                    eventsCount: 4,
                    year: 2025,
                    type: 'master',
                    visible: false,
                },
            ],
        ])
        expect(wrapper.findAll('.cup-type-icon')).not.toHaveLength(0)
    })

    it('shows field and form errors and exposes the pending state', () => {
        const wrapper = mount(CupForm, {
            props: {
                submitLabel: 'Стварыць',
                pending: true,
                error: 'Не атрымалася',
                errors: { name: 'Абавязковае поле' },
            },
            global: { plugins: [PrimeVue] },
        })

        expect(wrapper.find('.field-error').text()).toBe('Абавязковае поле')
        expect(wrapper.find('.p-message').text()).toContain('Не атрымалася')
        expect(wrapper.findComponent({ name: 'Button' }).props('loading')).toBe(
            true,
        )
    })
})
