import { beforeEach, describe, expect, it, vi } from 'vitest'
import { api } from './client'
import {
    clearClubOptionsCache,
    createClub,
    getClub,
    getClubOptions,
    getClubs,
    updateClub,
} from './clubs'

vi.mock('./client', () => ({
    api: { get: vi.fn(), post: vi.fn(), put: vi.fn() },
}))

describe('clubs api', () => {
    beforeEach(() => {
        vi.clearAllMocks()
        clearClubOptionsCache()
    })

    it('requests and caches all club options', async () => {
        vi.mocked(api.get).mockResolvedValue({
            data: [{ id: '1', name: 'Club' }],
            headers: {},
        })

        await expect(getClubOptions()).resolves.toEqual([
            { id: '1', name: 'Club' },
        ])
        await getClubOptions()

        expect(api.get).toHaveBeenCalledTimes(1)
        expect(api.get).toHaveBeenCalledWith('/clubs/all')
    })

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

    it('creates and updates a club through V1', async () => {
        vi.mocked(api.post).mockResolvedValue({ data: { id: '1' } })
        vi.mocked(api.put).mockResolvedValue({ data: { id: '1' } })

        await createClub({ name: 'Club' })
        await updateClub('1', { name: 'Updated' })

        expect(api.post).toHaveBeenCalledWith('/clubs', { name: 'Club' })
        expect(api.put).toHaveBeenCalledWith('/clubs/1', { name: 'Updated' })
    })
})
