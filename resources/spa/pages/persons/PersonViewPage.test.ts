// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'
import PersonViewPage from './PersonViewPage.vue'
import { personContextKey } from './personContext'

const { auth, getPersonProtocolLines, getYears, push } = vi.hoisted(() => ({
    auth: { isAuthenticated: true },
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
vi.mock('../../stores/auth', () => ({ useAuthStore: () => auth }))

const DataTableStub = {
    name: 'DataTableStub',
    props: ['value', 'rowClass'],
    template: '<div><slot /></div>',
}

describe('person view page', () => {
    beforeEach(() => {
        vi.resetAllMocks()
        auth.isAuthenticated = true
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
                    ActionButton: true,
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
                provide: {
                    [personContextKey as symbol]: ref({
                        id: '7',
                        lastname: 'Test',
                        firstname: 'Ivan',
                        birthday: '1990-01-01',
                        rankId: 0,
                        clubId: null,
                    }),
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
        expect(wrapper.findAll('.column')).toHaveLength(10)
    })

    it('shows an empty state', async () => {
        getYears.mockResolvedValue([])
        getPersonProtocolLines.mockResolvedValue({ data: [], headers: {} })

        const wrapper = mount(PersonViewPage, {
            global: {
                stubs: {
                    Button: true,
                    ActionButton: true,
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

    it('highlights mismatched protocol lines and adds the actions column', async () => {
        getYears.mockResolvedValue([])
        const mismatchedLine = {
            id: '11',
            personId: '7',
            firstname: 'John',
            lastname: 'Test',
            distanceId: '12',
            eventId: '13',
            competitionId: '14',
            competitionName: 'Spring Cup',
            eventName: 'Long',
            eventDate: '2026-05-10',
            groupName: 'M21',
            year: '1989',
            time: '01:02:03',
            place: '1',
            completeRank: 'II',
        }
        getPersonProtocolLines.mockResolvedValue({
            data: [mismatchedLine],
            headers: { 'x-pagination-total': '1' },
        })

        const wrapper = mount(PersonViewPage, {
            global: {
                stubs: {
                    ActionButton: true,
                    Button: true,
                    Column: {
                        props: ['header'],
                        template: '<div class="column">{{ header }}</div>',
                    },
                    DataTable: DataTableStub,
                    DateFilter: true,
                    FilterPanel: { template: '<div><slot /></div>' },
                    InputText: true,
                    Message: { template: '<div><slot /></div>' },
                    Paginator: true,
                    RouterLink: true,
                    YearFilter: true,
                },
                provide: {
                    [personContextKey as symbol]: ref({
                        id: '7',
                        lastname: 'Test',
                        firstname: 'Ivan',
                        birthday: '1990-01-01',
                        rankId: 0,
                        clubId: null,
                    }),
                },
            },
        })
        await flushPromises()

        const rowClass = wrapper
            .findComponent(DataTableStub)
            .props('rowClass') as (line: typeof mismatchedLine) => string

        expect(rowClass(mismatchedLine)).toBe('person-view-mismatch-row')
        expect(wrapper.text()).toContain('Дзеянні')
    })

    it('shows mismatched protocol lines but hides actions from anonymous visitors', async () => {
        auth.isAuthenticated = false
        getYears.mockResolvedValue([])
        const matchingLine = {
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
        }
        const mismatchedLine = { ...matchingLine, id: '12', firstname: 'John' }
        getPersonProtocolLines.mockResolvedValue({
            data: [matchingLine, mismatchedLine],
            headers: { 'x-pagination-total': '2' },
        })

        const wrapper = mount(PersonViewPage, {
            global: {
                stubs: {
                    ActionButton: true,
                    Button: true,
                    Column: true,
                    DataTable: DataTableStub,
                    DateFilter: true,
                    FilterPanel: { template: '<div><slot /></div>' },
                    InputText: true,
                    Message: { template: '<div><slot /></div>' },
                    Paginator: true,
                    RouterLink: true,
                    YearFilter: true,
                },
                provide: {
                    [personContextKey as symbol]: ref({
                        id: '7',
                        lastname: 'Test',
                        firstname: 'Ivan',
                        birthday: '1990-01-01',
                        rankId: 0,
                        clubId: null,
                    }),
                },
            },
        })
        await flushPromises()

        expect(wrapper.findComponent(DataTableStub).props('value')).toEqual([
            matchingLine,
            mismatchedLine,
        ])
        expect(
            wrapper.findAllComponents({ name: 'ActionButton' }),
        ).toHaveLength(0)

        const rowClass = wrapper
            .findComponent(DataTableStub)
            .props('rowClass') as (line: typeof mismatchedLine) => string
        expect(rowClass(mismatchedLine)).toBeUndefined()
    })

    it('keeps a short competition search in the input without requesting it', async () => {
        getYears.mockResolvedValue([])
        getPersonProtocolLines.mockResolvedValue({ data: [], headers: {} })

        const wrapper = mount(PersonViewPage, {
            global: {
                stubs: {
                    Button: true,
                    ActionButton: true,
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
