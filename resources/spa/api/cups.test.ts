import { describe, expect, it, vi } from 'vitest'
import { api } from './client'
import {
    createCupEvent,
    getCupEvent,
    getCupEvents,
    updateCupEvent,
} from './cups'

vi.mock('./client', () => ({
    api: { get: vi.fn(), post: vi.fn(), put: vi.fn() },
}))

describe('cups API', () => {
    it('passes compact cup-stage filters to the nested endpoint', async () => {
        vi.mocked(api.get).mockResolvedValue({ data: [], headers: {} })

        await getCupEvents('42', {
            eventIds: ['10', '20'],
            name: 'Stage',
            date: '2026-05-10',
            page: 2,
            perPage: 50,
        })

        expect(api.get).toHaveBeenCalledWith('/cups/42/events', {
            params: {
                eventIds: ['10', '20'],
                name: 'Stage',
                date: '2026-05-10',
                page: 2,
                perPage: 50,
            },
        })
    })

    it('uses aggregate endpoints for a cup stage', async () => {
        vi.mocked(api.get).mockResolvedValue({ data: { id: '7' } })
        vi.mocked(api.post).mockResolvedValue({ data: { id: '7' } })
        vi.mocked(api.put).mockResolvedValue({ data: { id: '7' } })

        await getCupEvent('7')
        await createCupEvent({ cupId: 42, eventId: 9, points: 100 })
        await updateCupEvent('7', { eventId: 9, points: 75 })

        expect(api.get).toHaveBeenCalledWith('/cup-events/7')
        expect(api.post).toHaveBeenCalledWith('/cup-events', {
            cupId: 42,
            eventId: 9,
            points: 100,
        })
        expect(api.put).toHaveBeenCalledWith('/cup-events/7', {
            eventId: 9,
            points: 75,
        })
    })
})
