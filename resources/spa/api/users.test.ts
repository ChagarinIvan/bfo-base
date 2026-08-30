import { beforeEach, describe, expect, it, vi } from 'vitest'
import { api } from './client'
import { clearUsersCache, getUsers } from './users'

vi.mock('./client', () => ({
    api: {
        get: vi.fn(),
    },
}))

describe('users API cache', () => {
    beforeEach(() => {
        clearUsersCache()
        vi.clearAllMocks()
    })

    it('caches users for one hour', async () => {
        const users = [{ id: 1, name: 'Admin', email: 'admin@example.com' }]
        vi.mocked(api.get).mockResolvedValue({ data: users })

        await expect(getUsers()).resolves.toEqual(users)
        await expect(getUsers()).resolves.toEqual(users)

        expect(api.get).toHaveBeenCalledTimes(1)
    })

    it('requests fresh users after the cache expires', async () => {
        const now = vi.spyOn(Date, 'now')
        now.mockReturnValueOnce(0).mockReturnValueOnce(30 * 60 * 1000)
        vi.mocked(api.get).mockResolvedValue({ data: [] })

        await getUsers()
        await getUsers()
        expect(api.get).toHaveBeenCalledTimes(1)

        now.mockReturnValue(60 * 60 * 1000 + 1)
        vi.mocked(api.get).mockResolvedValue({
            data: [{ id: 2, name: null, email: 'user@example.com' }],
        })
        await expect(getUsers()).resolves.toEqual([
            { id: 2, name: null, email: 'user@example.com' },
        ])
        expect(api.get).toHaveBeenCalledTimes(2)
        now.mockRestore()
    })
})
