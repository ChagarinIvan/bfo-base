import { describe, expect, it, vi } from 'vitest'
import { api } from './client'
import { getCompetitionEvents } from './events'

vi.mock('./client', () => ({
    api: {
        get: vi.fn(),
    },
}))

describe('events API', () => {
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
