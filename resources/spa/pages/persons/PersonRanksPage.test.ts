// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import PersonRanksPage from './PersonRanksPage.vue'

const {
    auth,
    getCupEventContexts,
    getEventsByIds,
    getPersonRankHistories,
    getRanks,
    rebuildPersonRanks,
    routerReplace,
} = vi.hoisted(() => ({
    auth: { isAuthenticated: true },
    getCupEventContexts: vi.fn(),
    getEventsByIds: vi.fn(),
    getPersonRankHistories: vi.fn(),
    getRanks: vi.fn(),
    rebuildPersonRanks: vi.fn(),
    routerReplace: vi.fn(),
}))

vi.mock('../../api/cups', () => ({ getCupEventContexts }))
vi.mock('../../api/events', () => ({ getEventsByIds }))
vi.mock('../../api/personRankHistory', async () => ({
    getPersonRankHistories,
    activatePersonRank: vi.fn(),
    updatePersonRankActivation: vi.fn(),
}))
vi.mock('../../api/ranks', () => ({ getRanks }))
vi.mock('../../api/persons', () => ({ rebuildPersonRanks }))
vi.mock('../../stores/auth', () => ({ useAuthStore: () => auth }))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { personId: '7' } }),
    useRouter: () => ({ replace: routerReplace }),
}))

const history = {
    id: '501',
    personId: '7',
    protocolLineId: '901',
    distanceId: '77',
    eventId: '12',
    competitionId: '3',
    rankId: 7,
    changeType: 'completion',
    achievedOn: '2024-06-01',
    activatedOn: '2024-06-15',
    startedOn: '2024-06-15',
    finishedOn: '2026-06-15',
}

const DataTableStub = {
    name: 'DataTableStub',
    props: ['value'],
    template: '<div><slot /></div>',
}

describe('person ranks page', () => {
    beforeEach(() => {
        vi.resetAllMocks()
        auth.isAuthenticated = true
        getCupEventContexts.mockResolvedValue([])
        getRanks.mockResolvedValue([{ id: 7, label: 'КМС' }])
        getPersonRankHistories.mockResolvedValue([history])
        getEventsByIds.mockResolvedValue([
            {
                id: '12',
                competitionId: '3',
                name: 'Final',
                description: '',
                date: '2024-05-20',
                participantsCount: 1,
                competitionName: 'Spring Cup',
            },
        ])
    })

    it('loads rank histories and event labels without filters', async () => {
        const wrapper = mount(PersonRanksPage, {
            global: {
                stubs: {
                    ActionButton: true,
                    Button: true,
                    Column: {
                        props: ['header'],
                        template: '<div class="column">{{ header }}</div>',
                    },
                    DataTable: { template: '<div><slot /></div>' },
                    Dialog: true,
                    Message: { template: '<div><slot /></div>' },
                    Paginator: true,
                    RouterLink: { template: '<a><slot /></a>' },
                },
            },
        })
        await flushPromises()

        expect(getPersonRankHistories).toHaveBeenCalledWith('7')
        expect(getEventsByIds).toHaveBeenCalledWith(['12'])
        expect(getCupEventContexts).not.toHaveBeenCalled()
        expect(getRanks).toHaveBeenCalledOnce()
        expect(wrapper.find('#person-rank-year-filter').exists()).toBe(false)
        expect(wrapper.find('#person-rank-filter').exists()).toBe(false)
        expect(wrapper.text()).toContain('Гісторыя разрадаў')
        expect(wrapper.find('.rank-history-group').exists()).toBe(true)
        expect(wrapper.find('.rank-history-timeline').exists()).toBe(false)
        await wrapper.find('.rank-history-group').trigger('click')
        await flushPromises()
        expect(getCupEventContexts).toHaveBeenCalledWith(['12'])
        expect(wrapper.find('.rank-history-timeline').exists()).toBe(true)
        expect(wrapper.findAll('.column')).toHaveLength(8)
        expect(wrapper.text()).toContain('Тып выканання')
    })

    it('hides the mutation column for anonymous users', async () => {
        auth.isAuthenticated = false
        getPersonRankHistories.mockResolvedValue([])
        getEventsByIds.mockResolvedValue([])

        const wrapper = mount(PersonRanksPage, {
            global: {
                stubs: {
                    Button: true,
                    Dialog: true,
                    Message: { template: '<div><slot /></div>' },
                    RouterLink: { template: '<a><slot /></a>' },
                },
            },
        })
        await flushPromises()

        expect(wrapper.text()).not.toContain('Актываваць разрад')
    })

    it('rebuilds ranks and refreshes history for an authenticated user', async () => {
        rebuildPersonRanks.mockResolvedValue(undefined)
        const wrapper = mount(PersonRanksPage, {
            global: {
                stubs: {
                    ActionButton: true,
                    Button: {
                        name: 'Button',
                        props: ['label', 'severity', 'icon', 'loading'],
                        template:
                            '<button @click="$emit(\'click\')">{{ label }}</button>',
                    },
                    Column: true,
                    DataTable: DataTableStub,
                    Dialog: true,
                    Message: { template: '<div><slot /></div>' },
                    RouterLink: true,
                },
            },
        })
        await flushPromises()
        const callsBeforeRebuild = getPersonRankHistories.mock.calls.length
        expect(wrapper.findComponent({ name: 'Button' }).props()).toMatchObject(
            {
                severity: 'success',
                icon: 'pi pi-refresh',
            },
        )
        await wrapper.get('button').trigger('click')
        await flushPromises()

        expect(rebuildPersonRanks).toHaveBeenCalledWith('7')
        expect(getPersonRankHistories.mock.calls.length).toBeGreaterThanOrEqual(
            callsBeforeRebuild + 1,
        )
        expect(routerReplace).toHaveBeenCalledWith({
            query: { refresh: expect.any(String) },
        })
    })

    it('keeps lower-rank achievements in their own rank period', async () => {
        getRanks.mockResolvedValue([
            { id: 7, label: 'КМС' },
            { id: 6, label: 'I' },
        ])
        const firstRank = {
            ...history,
            id: '1',
            rankId: 6,
            changeType: 'completion',
            achievedOn: '2019-03-07',
            activatedOn: '2019-03-07',
            startedOn: '2019-03-07',
            finishedOn: '2019-10-27',
        }
        const candidateMaster = {
            ...history,
            id: '2',
            rankId: 7,
            changeType: 'promotion',
            achievedOn: '2019-10-27',
            activatedOn: '2019-10-27',
            startedOn: '2019-10-27',
            finishedOn: '2021-10-27',
        }
        const lowerConfirmation = {
            ...firstRank,
            id: '3',
            changeType: 'lower_qualification',
            achievedOn: '2019-11-07',
            startedOn: '2021-10-27',
            finishedOn: '2022-03-15',
        }
        getPersonRankHistories.mockResolvedValue([
            lowerConfirmation,
            candidateMaster,
            firstRank,
        ])

        const wrapper = mount(PersonRanksPage, {
            global: {
                stubs: {
                    ActionButton: true,
                    Button: true,
                    Column: true,
                    DataTable: DataTableStub,
                    Dialog: true,
                    Message: { template: '<div><slot /></div>' },
                    RouterLink: true,
                },
            },
        })
        await flushPromises()

        const groups = wrapper.findAll('.rank-history-group')
        expect(groups).toHaveLength(3)
        await groups
            .find(
                (group) =>
                    group.text().includes('I') &&
                    group.text().includes('2021-10-27'),
            )!
            .trigger('click')

        expect(wrapper.findComponent(DataTableStub).props('value')).toEqual([
            lowerConfirmation,
        ])
    })

    it('keeps unconfirmed achievements outside active rank periods', async () => {
        const unconfirmedMaster = {
            ...history,
            id: '2',
            rankId: 8,
            achievedOn: '2026-08-02',
            activatedOn: null,
            startedOn: '2026-08-02',
            finishedOn: null,
        }
        getRanks.mockResolvedValue([
            { id: 7, label: 'КМС' },
            { id: 8, label: 'МС' },
        ])
        getPersonRankHistories.mockResolvedValue([history, unconfirmedMaster])

        const wrapper = mount(PersonRanksPage, {
            global: {
                stubs: {
                    ActionButton: true,
                    Button: true,
                    Column: true,
                    DataTable: DataTableStub,
                    Dialog: true,
                    Message: { template: '<div><slot /></div>' },
                    RouterLink: true,
                },
            },
        })
        await flushPromises()

        const groups = wrapper.findAll('.rank-history-group')
        expect(groups).toHaveLength(2)
        expect(groups[0]?.text()).toContain('КМС')
        expect(groups[1]?.text()).toContain('Чакаюць актывацыі')
        await groups[1]?.trigger('click')
        expect(wrapper.findComponent(DataTableStub).props('value')).toEqual([
            unconfirmedMaster,
        ])
    })
})
