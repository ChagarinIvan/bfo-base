import { beforeEach, describe, expect, it, vi } from 'vitest'
import { api } from './client'
import { getPersons } from './persons'

vi.mock('./client', () => ({
    api: { get: vi.fn() },
}))

describe('persons api', () => {
    beforeEach(() => vi.clearAllMocks())

    it('requests paginated compact persons with the optional camelCase club filter', async () => {
        const headers = { 'x-pagination-total': '1' }
        vi.mocked(api.get).mockResolvedValue({
            data: [
                {
                    id: '42',
                    lastname: 'Ivanov',
                    firstname: 'Ivan',
                    birthday: '2001-06-04',
                    rankId: 0,
                    clubId: '7',
                },
            ],
            headers,
        })

        await expect(
            getPersons({
                name: 'Ivan',
                clubId: 7,
                rankId: 0,
                birthYear: 2001,
                page: 2,
                perPage: 10,
            }),
        ).resolves.toEqual({
            data: [
                {
                    id: '42',
                    lastname: 'Ivanov',
                    firstname: 'Ivan',
                    birthday: '2001-06-04',
                    rankId: 0,
                    clubId: '7',
                },
            ],
            headers,
        })
        expect(api.get).toHaveBeenCalledWith('/persons', {
            params: {
                name: 'Ivan',
                clubId: 7,
                rankId: 0,
                birthYear: 2001,
                page: 2,
                perPage: 10,
            },
        })
    })
})
