import { beforeEach, describe, expect, it, vi } from 'vitest'
import { api } from './client'
import { clearRanksCache, getRanks } from './ranks'

vi.mock('./client', () => ({ api: { get: vi.fn() } }))

describe('ranks api', () => {
    beforeEach(() => {
        vi.clearAllMocks()
        clearRanksCache()
    })

    it('loads and caches rank options', async () => {
        const data = [{ id: 'first_rank', label: 'I' }]
        vi.mocked(api.get).mockResolvedValue({ data })

        await expect(getRanks()).resolves.toEqual(data)
        await expect(getRanks()).resolves.toEqual(data)
        expect(api.get).toHaveBeenCalledTimes(1)
        expect(api.get).toHaveBeenCalledWith('/ranks')
    })
})
