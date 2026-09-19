import { describe, expect, it, vi } from 'vitest'
import { api } from './client'
import { getCupEvents } from './cups'

vi.mock('./client', () => ({ api: { get: vi.fn() } }))

describe('cups API', () => {
    it('passes compact cup-stage filters to the nested endpoint', async () => {
        vi.mocked(api.get).mockResolvedValue({ data: [], headers: {} })

        await getCupEvents('42', {
            eventIds: ['10', '20'],
            name: 'Stage',
            date: '2026-05-10',
            page: 2,
            perPage: 50,
        })

        expect(api.get).toHaveBeenCalledWith('/cups/42/events', {
            params: {
                eventIds: ['10', '20'],
                name: 'Stage',
                date: '2026-05-10',
                page: 2,
                perPage: 50,
            },
        })
    })
})
