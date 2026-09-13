// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
import { describe, expect, it } from 'vitest'
import EventProcessingStatus from './EventProcessingStatus.vue'

describe('event processing status', () => {
    it('shows a warning while athlete identification is pending', () => {
        const wrapper = mount(EventProcessingStatus, {
            props: { status: 'identifying' },
            global: { plugins: [PrimeVue] },
        })

        expect(wrapper.text()).toContain('распазнаванне ўдзельнікаў')
        expect(wrapper.find('.p-progressspinner').exists()).toBe(true)
    })

    it('shows the terminal parser error without a spinner', () => {
        const wrapper = mount(EventProcessingStatus, {
            props: { status: 'failed' },
            global: { plugins: [PrimeVue] },
        })

        expect(wrapper.text()).toContain('Памылка парсінгу пратаколу')
        expect(wrapper.find('.p-progressspinner').exists()).toBe(false)
    })
})
