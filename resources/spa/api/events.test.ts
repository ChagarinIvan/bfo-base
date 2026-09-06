import { describe, expect, it, vi } from 'vitest'
import { api } from './client'
import { getCompetitionEvents, getEventsByIds } from './events'

vi.mock('./client', () => ({
    api: {
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
})
