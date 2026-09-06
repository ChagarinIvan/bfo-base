// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import PrimeVue from 'primevue/config'
import PersonPaymentForm from './PersonPaymentForm.vue'

describe('person payment form', () => {
    it('emits the selected date and shows validation state', async () => {
        const wrapper = mount(PersonPaymentForm, {
            props: {
                initialValue: { date: '2025-01-01' },
                errors: { date: 'Укажыце дату.' },
            },
            global: {
                plugins: [PrimeVue],
                stubs: {
                    DateFilter: {
                        props: ['modelValue', 'error'],
                        template:
                            '<div><input id="payment-date" :value="modelValue" />{{ error }}</div>',
                    },
                },
            },
        })

        expect(wrapper.find('#payment-date').attributes('value')).toBe(
            '2025-01-01',
        )
        expect(wrapper.text()).toContain('Укажыце дату.')

        await wrapper.find('form').trigger('submit')

        expect(wrapper.emitted('submit')).toEqual([[{ date: '2025-01-01' }]])
    })

    it('disables the date field while saving', () => {
        const wrapper = mount(PersonPaymentForm, {
            props: { pending: true },
            global: {
                plugins: [PrimeVue],
                stubs: {
                    DateFilter: {
                        props: ['disabled'],
                        template: '<input :disabled="disabled" />',
                    },
                },
            },
        })

        expect(wrapper.find('input').attributes('disabled')).toBeDefined()
    })
})
