import { beforeEach, describe, expect, it, vi } from 'vitest'
import { api } from './client'
import { clearYearsCache, getYears } from './years'

vi.mock('./client', () => ({
    api: {
        get: vi.fn(),
    },
}))

describe('years API cache', () => {
    beforeEach(() => {
        clearYearsCache()
        vi.clearAllMocks()
    })

    it('caches years for one hour', async () => {
        vi.mocked(api.get).mockResolvedValue({ data: [2026, 2025] })

        await expect(getYears()).resolves.toEqual([2026, 2025])
        await expect(getYears()).resolves.toEqual([2026, 2025])

        expect(api.get).toHaveBeenCalledTimes(1)
    })

    it('requests fresh years after the cache expires', async () => {
        const now = vi.spyOn(Date, 'now')
        now.mockReturnValueOnce(0).mockReturnValueOnce(30 * 60 * 1000)
        vi.mocked(api.get).mockResolvedValue({ data: [2026] })

        await getYears()
        await getYears()
        expect(api.get).toHaveBeenCalledTimes(1)

        now.mockReturnValue(60 * 60 * 1000 + 1)
        vi.mocked(api.get).mockResolvedValue({ data: [2025] })
        await expect(getYears()).resolves.toEqual([2025])
        expect(api.get).toHaveBeenCalledTimes(2)
        now.mockRestore()
    })
})
