// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import RankCheckViewPage from './RankCheckViewPage.vue'
import { t } from '../../i18n'

const { getRankCheck, listRankCheckRows, getUsers } = vi.hoisted(() => ({
    getRankCheck: vi.fn(),
    listRankCheckRows: vi.fn(),
    getUsers: vi.fn(),
}))

vi.mock('../../api/rankChecks', () => ({ getRankCheck, listRankCheckRows }))
vi.mock('../../api/users', () => ({ getUsers }))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { rankCheckId: '20' } }),
}))

const stubs = {
    Card: { template: '<div><slot name="content" /></div>' },
    ImpressionDetails: { template: '<span />' },
    ListingTable: {
        props: ['items'],
        template: `<div><div v-for="item in items" :key="item.position">
            <div class="club-cell"><slot name="cell-club" :data="item" /></div>
            <div class="rank-cell"><slot name="cell-rank" :data="item" /></div>
            <div class="year-cell"><slot name="cell-year" :data="item" /></div>
        </div></div>`,
    },
    RouterLink: { template: '<a><slot /></a>' },
    Tag: { template: '<span />' },
}

describe('rank check result empty values', () => {
    beforeEach(() => {
        vi.clearAllMocks()
        getRankCheck.mockResolvedValue({
            id: '20',
            status: 'READY',
            created: { at: '2026-10-02T23:00:00+00:00', by: '1' },
            updated: { at: '2026-10-02T23:01:00+00:00', by: '1' },
        })
        getUsers.mockResolvedValue([])
        listRankCheckRows.mockResolvedValue({
            data: [
                {
                    position: 1,
                    club: 'лично',
                    databaseClub: null,
                    rank: null,
                    databaseRank: null,
                    year: null,
                    databaseYear: null,
                    hasPerson: true,
                },
            ],
            pagination: { currentPage: 1, perPage: 20, hasNext: false },
        })
    })

    it('shows an empty value for a missing database club and keeps the mismatch', async () => {
        const wrapper = mount(RankCheckViewPage, {
            global: { stubs, directives: { tooltip: {} } },
        })
        try {
            await flushPromises()

            const club = wrapper.get('.club-cell')
            expect(club.get('.rank-check-value--mismatch').text()).toBe('лично')
            expect(club.get('.rank-check-value__correct').text()).toBe('пуста')
            expect(club.text()).not.toContain(t('spa.rank_check.empty'))
            expect(wrapper.get('.rank-cell').text()).toBe('пуста')
            expect(wrapper.get('.year-cell').text()).toBe('пуста')
            expect(t('spa.rank_check.empty')).toBe(
                'Праверкі разрадаў яшчэ не запускаліся.',
            )
        } finally {
            wrapper.unmount()
        }
    })
})
