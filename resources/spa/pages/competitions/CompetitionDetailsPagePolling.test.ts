// @vitest-environment happy-dom

import { flushPromises, shallowMount } from '@vue/test-utils'
import { afterEach, describe, expect, it, vi } from 'vitest'
import CompetitionDetailsPage from './CompetitionDetailsPage.vue'

const { getCompetition, getCompetitionEvents, getCupEventContexts } =
    vi.hoisted(() => ({
        getCompetition: vi.fn(),
        getCompetitionEvents: vi.fn(),
        getCupEventContexts: vi.fn(),
    }))

vi.mock('../../api/competitions', () => ({
    getCompetition,
    deleteCompetition: vi.fn(),
}))
vi.mock('../../api/events', () => ({
    getCompetitionEvents,
    deleteEvent: vi.fn(),
}))
vi.mock('../../api/cups', () => ({ getCupEventContexts }))
vi.mock('../../api/users', () => ({ getUsers: vi.fn().mockResolvedValue([]) }))
vi.mock('../../stores/auth', () => ({
    useAuthStore: () => ({ isAuthenticated: true }),
}))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { id: '42' } }),
    useRouter: () => ({ replace: vi.fn() }),
}))

const headers = {
    'x-pagination-current-page': '1',
    'x-pagination-per-page': '20',
    'x-pagination-has-next': 'false',
}

describe('competition event processing polling', () => {
    afterEach(() => {
        vi.useRealTimers()
        vi.resetAllMocks()
    })

    it('refreshes transitional events every five seconds and stops at an error', async () => {
        vi.useFakeTimers()
        getCompetition.mockResolvedValue({ id: '42', name: 'Спаборніцтва' })
        getCupEventContexts.mockResolvedValue([])
        getCompetitionEvents
            .mockResolvedValueOnce({
                data: [{ id: '7', processingStatus: 'parsing' }],
                headers,
            })
            .mockResolvedValueOnce({
                data: [
                    {
                        id: '7',
                        processingStatus: 'parsingError',
                        errorMessage: 'Памылка',
                    },
                ],
                headers,
            })

        const wrapper = shallowMount(CompetitionDetailsPage)
        await flushPromises()
        expect(getCompetitionEvents).toHaveBeenCalledTimes(1)

        await vi.advanceTimersByTimeAsync(5000)
        await flushPromises()
        expect(getCompetitionEvents).toHaveBeenCalledTimes(2)

        await vi.advanceTimersByTimeAsync(5000)
        expect(getCompetitionEvents).toHaveBeenCalledTimes(2)
        wrapper.unmount()
    })

    it('stops refreshing when the page is closed', async () => {
        vi.useFakeTimers()
        getCompetition.mockResolvedValue({ id: '42', name: 'Спаборніцтва' })
        getCupEventContexts.mockResolvedValue([])
        getCompetitionEvents.mockResolvedValue({
            data: [{ id: '7', processingStatus: 'identifying' }],
            headers,
        })

        const wrapper = shallowMount(CompetitionDetailsPage)
        await flushPromises()
        wrapper.unmount()
        await vi.advanceTimersByTimeAsync(5000)

        expect(getCompetitionEvents).toHaveBeenCalledTimes(1)
    })
})
