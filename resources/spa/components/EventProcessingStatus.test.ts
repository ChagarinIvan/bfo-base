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

    it.each([
        ['parsingError', 'Памылка разбору пратаколу'],
        ['identifyingError', 'Памылка распазнавання ўдзельнікаў'],
        ['rebuildingRanksError', 'Памылка абнаўлення разрадаў'],
    ] as const)(
        'shows the %s fallback without a spinner',
        (status, message) => {
            const wrapper = mount(EventProcessingStatus, {
                props: { status },
                global: { plugins: [PrimeVue] },
            })

            expect(wrapper.text()).toContain(message)
            expect(wrapper.find('.p-progressspinner').exists()).toBe(false)
        },
    )

    it('shows the safe error message returned by the API', () => {
        const wrapper = mount(EventProcessingStatus, {
            props: {
                status: 'identifyingError',
                errorMessage: 'Пратакол не апрацаваны.',
            },
            global: { plugins: [PrimeVue] },
        })

        expect(wrapper.text()).toContain('Пратакол не апрацаваны.')
        expect(wrapper.text()).not.toContain('Памылка распазнавання')
    })
})
