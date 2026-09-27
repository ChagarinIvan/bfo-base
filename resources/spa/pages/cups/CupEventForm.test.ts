// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
import Select from 'primevue/select'
import { describe, expect, it } from 'vitest'
import CupEventForm from './CupEventForm.vue'

describe('cup event form', () => {
    it('keeps the event picker searchable and shows a placeholder before selection', () => {
        const wrapper = mount(CupEventForm, {
            props: { events: [], submitLabel: 'Захаваць' },
            global: { plugins: [PrimeVue] },
        })
        const picker = wrapper.findComponent(Select)

        expect(picker.props('filter')).toBe(true)
        expect(picker.props('filterFields')).toEqual([
            'name',
            'competitionName',
        ])
        expect(picker.props('placeholder')).toBe('Абярыце спаборніцтва')
    })

    it('emits the prefilled event and points', async () => {
        const wrapper = mount(CupEventForm, {
            props: {
                events: [
                    {
                        id: '9',
                        competitionId: '1',
                        name: 'Этап',
                        description: '',
                        date: '2026-01-01',
                        participantsCount: 0,
                        competitionName: 'Кубак',
                    },
                ],
                initialValue: { eventId: 9, points: 75 },
                submitLabel: 'Захаваць',
            },
            global: { plugins: [PrimeVue] },
        })

        await wrapper.find('form').trigger('submit')

        expect(wrapper.emitted('submit')).toEqual([
            [{ eventId: 9, points: 75 }],
        ])
    })
})
