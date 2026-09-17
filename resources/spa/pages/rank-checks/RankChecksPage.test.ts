// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import RankChecksPage from './RankChecksPage.vue'

const { getRankChecks, getUsers, push } = vi.hoisted(() => ({
    getRankChecks: vi.fn(),
    getUsers: vi.fn(),
    push: vi.fn(),
}))

vi.mock('../../api/rankChecks', () => ({ getRankChecks }))
vi.mock('../../api/users', () => ({ getUsers }))
vi.mock('vue-router', () => ({
    RouterLink: { template: '<a><slot /></a>' },
    useRouter: () => ({ push }),
}))

const stubs = {
    Button: { template: '<button><slot /></button>' },
    ImpressionDetails: { template: '<span />' },
    ListingTable: { template: '<div />' },
    RouterLink: { template: '<a><slot /></a>' },
    Tag: { template: '<span><slot /></span>' },
    Toolbar: {
        template: '<div><slot name="start" /><slot name="end" /></div>',
    },
}

const response = {
    data: [
        {
            id: '42',
            status: 'PARSING' as const,
            created: { at: '2026-09-17T20:00:00+00:00', by: '1' },
            updated: { at: '2026-09-17T20:00:00+00:00', by: '1' },
        },
    ],
    headers: {
        'x-pagination-current-page': '1',
        'x-pagination-per-page': '20',
        'x-pagination-total': '1',
        'x-pagination-last-page': '1',
    },
}

describe('rank checks page', () => {
    beforeEach(() => {
        vi.useFakeTimers()
        vi.clearAllMocks()
        getRankChecks.mockResolvedValue(response)
        getUsers.mockResolvedValue([])
    })

    afterEach(() => {
        vi.useRealTimers()
    })

    it('polls pending checks and stops polling after leaving the page', async () => {
        const wrapper = mount(RankChecksPage, { global: { stubs } })

        await flushPromises()
        expect(getRankChecks).toHaveBeenCalledTimes(1)

        vi.advanceTimersByTime(5000)
        await flushPromises()
        expect(getRankChecks).toHaveBeenCalledTimes(2)

        wrapper.unmount()
        vi.advanceTimersByTime(10000)
        await flushPromises()

        expect(getRankChecks).toHaveBeenCalledTimes(2)
    })
})
