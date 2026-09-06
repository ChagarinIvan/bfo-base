// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import PersonViewPage from './PersonViewPage.vue'

const { auth, getPersonProtocolLines, getYears, push } = vi.hoisted(() => ({
    auth: { isAuthenticated: true },
    getPersonProtocolLines: vi.fn(),
    getYears: vi.fn(),
    push: vi.fn(),
}))

vi.mock('../../api/protocolLines', () => ({ getPersonProtocolLines }))
vi.mock('../../api/years', () => ({ getYears }))
vi.mock('../../stores/auth', () => ({
    useAuthStore: () => auth,
}))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { personId: '7' } }),
    useRouter: () => ({ push }),
}))

describe('person view page', () => {
    beforeEach(() => {
        vi.resetAllMocks()
        auth.isAuthenticated = true
    })

    it('loads participation with both related resources and renders actions', async () => {
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
                    Column: true,
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
        expect(wrapper.text()).toContain('Рэдагаваць')
        expect(wrapper.text()).toContain('Промпты')
        expect(wrapper.text()).toContain('Аплаты')
        expect(wrapper.text()).toContain('Разрады')
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
                    FilterPanel: true,
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

    it('keeps only the public ranks action for anonymous visitors', async () => {
        auth.isAuthenticated = false
        getYears.mockResolvedValue([])
        getPersonProtocolLines.mockResolvedValue({ data: [], headers: {} })

        const wrapper = mount(PersonViewPage, {
            global: {
                stubs: {
                    Button: {
                        props: ['label'],
                        template: '<button>{{ label }}</button>',
                    },
                    Column: true,
                    DataTable: true,
                    DateFilter: true,
                    FilterPanel: true,
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

        expect(wrapper.text()).toContain('Разрады')
        expect(wrapper.text()).not.toContain('Рэдагаваць')
        expect(wrapper.text()).not.toContain('Промпты')
        expect(wrapper.text()).not.toContain('Аплаты')
    })
})
