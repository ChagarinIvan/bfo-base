import { beforeEach, describe, expect, it, vi } from 'vitest'
import { api } from './client'
import { getClub, getClubs } from './clubs'

vi.mock('./client', () => ({
    api: { get: vi.fn() },
}))

describe('clubs api', () => {
    beforeEach(() => vi.clearAllMocks())

    it('requests the paginated club listing and returns response metadata', async () => {
        const headers = { 'x-pagination-total': '2' }
        vi.mocked(api.get).mockResolvedValue({
            data: [{ id: '1', name: 'Club', personsCount: 0 }],
            headers,
        })

        await expect(
            getClubs({ name: 'Minsk', page: 2, perPage: 10 }),
        ).resolves.toEqual({
            data: [{ id: '1', name: 'Club', personsCount: 0 }],
            headers,
        })
        expect(api.get).toHaveBeenCalledWith('/clubs', {
            params: { name: 'Minsk', page: 2, perPage: 10 },
        })
    })

    it('requests one club by its identifier', async () => {
        vi.mocked(api.get).mockResolvedValue({
            data: { id: '42', name: 'Club', personsCount: 3 },
        })

        await expect(getClub('42')).resolves.toEqual({
            id: '42',
            name: 'Club',
            personsCount: 3,
        })
        expect(api.get).toHaveBeenCalledWith('/clubs/42')
    })
})
