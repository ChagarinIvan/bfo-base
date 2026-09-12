// @vitest-environment happy-dom

import { describe, expect, it, vi } from 'vitest'
import { api } from './client'
import {
    createEvent,
    getCompetitionEvents,
    getEventsByIds,
    updateEvent,
} from './events'

vi.mock('./client', () => ({
    api: {
        post: vi.fn(),
        put: vi.fn(),
        get: vi.fn(),
    },
}))

describe('events API', () => {
    it('loads events by ids with competition names', async () => {
        vi.mocked(api.get).mockResolvedValue({ data: [] })

        await expect(getEventsByIds(['12', '13'])).resolves.toEqual([])
        expect(api.get).toHaveBeenCalledWith('/events', {
            params: {
                ids: ['12', '13'],
                withCompetition: 1,
                perPage: 2,
            },
        })
    })

    it('uses the camelCase competitionId query parameter', async () => {
        vi.mocked(api.get).mockResolvedValue({ data: [], headers: {} })

        await expect(getCompetitionEvents('42')).resolves.toEqual({
            data: [],
            headers: {},
        })
        expect(api.get).toHaveBeenCalledWith('/events', {
            params: { competitionId: '42', page: 1, perPage: 20 },
        })
    })

    it('sends event creation and updates as multipart form data', async () => {
        vi.mocked(api.post).mockResolvedValue({ data: { id: '5' } })
        vi.mocked(api.put).mockResolvedValue({ data: { id: '5' } })
        const payload = {
            name: 'Stage',
            description: 'Description',
            date: '2026-05-10',
            url: 'https://obelarus.net/protocol',
        }

        await createEvent('42', payload)
        await updateEvent('5', payload)

        const creation = vi.mocked(api.post).mock.calls[0]
        expect(creation?.[0]).toBe('/competitions/42/events')
        expect(creation?.[1]).toBeInstanceOf(FormData)
        expect((creation?.[1] as FormData).get('url')).toBe(payload.url)
        expect(api.put).toHaveBeenCalledWith('/events/5', expect.any(FormData))
    })

})
