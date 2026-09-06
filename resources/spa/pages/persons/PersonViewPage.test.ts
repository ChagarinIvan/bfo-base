// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import PersonViewPage from './PersonViewPage.vue'

const { getPersonProtocolLines, getYears, push } = vi.hoisted(() => ({
    getPersonProtocolLines: vi.fn(),
    getYears: vi.fn(),
    push: vi.fn(),
}))

vi.mock('../../api/protocolLines', () => ({ getPersonProtocolLines }))
vi.mock('../../api/years', () => ({ getYears }))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { personId: '7' } }),
    useRouter: () => ({ push }),
}))

describe('person view page', () => {
    beforeEach(() => {
        vi.resetAllMocks()
    })

    it('loads participation with both related resources', async () => {
        getYears.mockResolvedValue([2026, 2025])
        getPersonProtocolLines.mockResolvedValue({
            data: [
                {
                    id: '11',
                    personId: '7',
                    firstname: 'Ivan',
                    lastname: 'Test',
                    distanceId: '12',
                    eventId: '13',
                    competitionId: '14',
                    competitionName: 'Spring Cup',
                    eventName: 'Long',
                    eventDate: '2026-05-10',
                    groupName: 'M21',
                    year: '1990',
                    time: '01:02:03',
                    place: '1',
                    completeRank: 'II',
                },
            ],
            headers: { 'x-pagination-total': '1' },
        })

        const wrapper = mount(PersonViewPage, {
            global: {
                stubs: {
                    Button: {
                        props: ['label'],
                        template: '<button>{{ label }}</button>',
                    },
                    Column: {
                        props: ['header'],
                        template: '<div class="column">{{ header }}</div>',
                    },
                    DataTable: { template: '<div><slot /></div>' },
                    DateFilter: true,
                    FilterPanel: { template: '<div><slot /></div>' },
                    InputText: true,
                    Message: { template: '<div><slot /></div>' },
                    Paginator: true,
                    PersonPromptPersonInfo: true,
                    RouterLink: { template: '<a><slot /></a>' },
                    YearFilter: true,
                },
            },
        })
        await flushPromises()

        expect(getPersonProtocolLines).toHaveBeenCalledWith({
            personId: '7',
            withEvent: 1,
            withCompetition: 1,
            page: 1,
            perPage: 20,
        })
        expect(wrapper.text()).toContain('Удзел у спаборніцтвах')
        expect(wrapper.findAll('.column')).toHaveLength(9)
    })

    it('shows an empty state', async () => {
        getYears.mockResolvedValue([])
        getPersonProtocolLines.mockResolvedValue({ data: [], headers: {} })

        const wrapper = mount(PersonViewPage, {
            global: {
                stubs: {
                    Button: true,
                    Column: true,
                    DataTable: true,
                    DateFilter: true,
                    FilterPanel: { template: '<div><slot /></div>' },
                    InputText: true,
                    Message: { template: '<div><slot /></div>' },
                    Paginator: true,
                    PersonPromptPersonInfo: true,
                    RouterLink: { template: '<a><slot /></a>' },
                    YearFilter: true,
                },
            },
        })
        await flushPromises()

        expect(wrapper.text()).toContain('Удзелы не знойдзены.')
    })

    it('keeps a short competition search in the input without requesting it', async () => {
        getYears.mockResolvedValue([])
        getPersonProtocolLines.mockResolvedValue({ data: [], headers: {} })

        const wrapper = mount(PersonViewPage, {
            global: {
                stubs: {
                    Button: true,
                    Column: true,
                    DataTable: true,
                    DateFilter: true,
                    FilterPanel: { template: '<div><slot /></div>' },
                    InputText: {
                        props: ['modelValue'],
                        emits: ['update:modelValue'],
                        template:
                            '<input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
                    },
                    Message: { template: '<div><slot /></div>' },
                    Paginator: true,
                    PersonPromptPersonInfo: true,
                    RouterLink: true,
                    YearFilter: true,
                },
            },
        })
        await flushPromises()

        await wrapper.find('input').setValue('a')

        expect(wrapper.find('input').element).toHaveProperty('value', 'a')
        expect(getPersonProtocolLines).toHaveBeenCalledTimes(1)
    })
})
