// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
import Select from 'primevue/select'
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

    it('uses server search without pagination and keeps the selected label when options change', async () => {
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
            },
            global: { plugins: [PrimeVue] },
        })

        const picker = wrapper.findComponent(Select)
        expect(picker.props('filter')).toBe(true)
        expect(wrapper.find('#cup-event-search').exists()).toBe(false)
        picker.vm.$emit('filter', { value: 'Forest' })
        expect(wrapper.emitted('update:search')).toEqual([['Forest']])

        picker.vm.$emit('update:modelValue', '9')
        await wrapper.vm.$nextTick()
        await wrapper.setProps({ events: [] })
        expect(wrapper.text()).toContain('Forest Cup — Sprint')
        await wrapper.find('form').trigger('submit')
        expect(wrapper.emitted('submit')).toEqual([[{ eventId: 9, points: 0 }]])

        picker.vm.show()
        await wrapper.vm.$nextTick()
        expect(wrapper.findComponent(SlicePaginator).exists()).toBe(false)
    })

    it('does not re-filter server results inside the picker', async () => {
        const wrapper = mount(CupEventForm, {
            props: {
                events: [
                    {
                        id: '9',
                        competitionId: '1',
                        name: 'Forest Sprint',
                        description: '',
                        date: '2026-01-01',
                        participantsCount: 0,
                        competitionName: 'Forest Cup',
                    },
                ],
                submitLabel: 'Захаваць',
                remoteSearch: true,
                search: ' Forest ',
            },
            global: { plugins: [PrimeVue] },
        })
        const picker = wrapper.findComponent(Select)

        await picker.setData({ filterValue: ' Forest ' })

        expect(
            (picker.vm as unknown as { visibleOptions: unknown[] })
                .visibleOptions,
        ).toHaveLength(1)
    })
})
