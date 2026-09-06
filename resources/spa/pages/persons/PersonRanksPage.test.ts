// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import PersonRanksPage from './PersonRanksPage.vue'

const { auth, getEventsByIds, getPersonRankHistories, getRanks } = vi.hoisted(
    () => ({
        auth: { isAuthenticated: true },
        getEventsByIds: vi.fn(),
        getPersonRankHistories: vi.fn(),
        getRanks: vi.fn(),
    }),
)

vi.mock('../../api/events', () => ({ getEventsByIds }))
vi.mock('../../api/personRankHistory', async () => ({
    getPersonRankHistories,
    activatePersonRank: vi.fn(),
    updatePersonRankActivation: vi.fn(),
}))
vi.mock('../../api/ranks', () => ({ getRanks }))
vi.mock('../../stores/auth', () => ({ useAuthStore: () => auth }))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { personId: '7' } }),
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

describe('person ranks page', () => {
    beforeEach(() => {
        vi.resetAllMocks()
        auth.isAuthenticated = true
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
        expect(getRanks).toHaveBeenCalledOnce()
        expect(wrapper.find('#person-rank-year-filter').exists()).toBe(false)
        expect(wrapper.find('#person-rank-filter').exists()).toBe(false)
        expect(wrapper.text()).toContain('Гісторыя разрадаў')
        expect(wrapper.find('.rank-history-group-count').text()).toBe('1')
        expect(wrapper.text()).toContain('2024-06-15')
        expect(wrapper.text()).toContain('2026-06-15')
        await wrapper.find('.rank-history-group').trigger('click')
        expect(wrapper.findAll('.column')).toHaveLength(7)
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
})
