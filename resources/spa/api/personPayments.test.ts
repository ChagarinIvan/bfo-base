import { beforeEach, describe, expect, it, vi } from 'vitest'
import { api } from './client'
import {
    createOrUpdatePersonPayment,
    getPersonPayments,
} from './personPayments'

vi.mock('./client', () => ({
    api: {
        get: vi.fn(),
        post: vi.fn(),
    },
}))

describe('person payments api', () => {
    beforeEach(() => vi.clearAllMocks())

    it('loads a paginated, year-filtered list for a person', async () => {
        vi.mocked(api.get).mockResolvedValue({
            data: [
                { id: '1', personId: '7', year: '2025', date: '2025-01-01' },
            ],
            headers: { 'x-pagination-total': '1' },
        })

        await expect(
            getPersonPayments({
                personId: '7',
                year: 2025,
                page: 2,
                perPage: 10,
            }),
        ).resolves.toEqual({
            data: [
                { id: '1', personId: '7', year: '2025', date: '2025-01-01' },
            ],
            headers: { 'x-pagination-total': '1' },
        })
        expect(api.get).toHaveBeenCalledWith('/persons/payments', {
            params: { personId: '7', year: 2025, page: 2, perPage: 10 },
        })
    })

    it('creates or updates with personId in the request body', async () => {
        vi.mocked(api.post).mockResolvedValue({
            data: { id: '1', personId: '7', year: '2025', date: '2025-02-01' },
        })

        await createOrUpdatePersonPayment('7', { date: '2025-02-01' })

        expect(api.post).toHaveBeenCalledWith('/persons/payments', {
            personId: '7',
            date: '2025-02-01',
        })
    })
})
