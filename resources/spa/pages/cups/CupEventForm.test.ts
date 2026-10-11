// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
import Select from 'primevue/select'
import InputText from 'primevue/inputtext'
import { describe, expect, it } from 'vitest'
import SlicePaginator from '../../components/SlicePaginator.vue'
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

    it('uses server search and keeps the selected label when the page changes', async () => {
        const event = {
            id: '9',
            competitionId: '1',
            name: 'Sprint',
            description: '',
            date: '2026-01-01',
            participantsCount: 0,
            competitionName: 'Forest Cup',
        }
        const wrapper = mount(CupEventForm, {
            props: {
                events: [event],
                submitLabel: 'Захаваць',
                remoteSearch: true,
                search: '',
                pagination: { currentPage: 1, perPage: 20, hasNext: true },
            },
            global: { plugins: [PrimeVue] },
        })

        expect(wrapper.findComponent(Select).props('filter')).toBe(false)
        wrapper.findComponent(InputText).vm.$emit('update:modelValue', 'Forest')
        expect(wrapper.emitted('update:search')).toEqual([['Forest']])

        wrapper.findComponent(Select).vm.$emit('update:modelValue', '9')
        await wrapper.vm.$nextTick()
        await wrapper.setProps({ events: [] })
        expect(wrapper.text()).toContain('Forest Cup — Sprint')
        await wrapper.find('form').trigger('submit')
        expect(wrapper.emitted('submit')).toEqual([[{ eventId: 9, points: 0 }]])

        wrapper.findComponent(SlicePaginator).vm.$emit('page', {
            page: 1,
            rows: 20,
        })
        expect(wrapper.emitted('page')).toHaveLength(1)
    })
})
