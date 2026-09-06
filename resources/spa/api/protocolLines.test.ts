import { describe, expect, it, vi } from 'vitest'
import { api } from './client'
import { getPersonProtocolLines } from './protocolLines'

vi.mock('./client', () => ({
    api: { get: vi.fn() },
}))

describe('protocol lines API', () => {
    it('requests person lines with event and competition resources', async () => {
        vi.mocked(api.get).mockResolvedValue({ data: [], headers: {} })

        await expect(
            getPersonProtocolLines({
                personId: '7',
                withEvent: 1,
                withCompetition: 1,
                year: 2026,
                competitionName: 'Spring',
                date: '2026-05-10',
                page: 2,
                perPage: 10,
            }),
        ).resolves.toEqual({ data: [], headers: {} })

        expect(api.get).toHaveBeenCalledWith('/protocol-lines', {
            params: {
                personId: '7',
                withEvent: 1,
                withCompetition: 1,
                year: 2026,
                competitionName: 'Spring',
                date: '2026-05-10',
                page: 2,
                perPage: 10,
            },
        })
    })
})
