import { describe, expect, it, vi } from 'vitest'
import { api } from './client'
import {
    activatePersonRank,
    addManualPersonRank,
    deleteManualPersonRank,
    getPersonRankHistories,
    updatePersonRankActivation,
} from './personRankHistory'

vi.mock('./client', () => ({
    api: { get: vi.fn(), post: vi.fn(), put: vi.fn(), delete: vi.fn() },
}))

describe('person rank history api', () => {
    it('loads all histories for a person', async () => {
        vi.mocked(api.get).mockResolvedValue({ data: [] })

        await expect(getPersonRankHistories('7')).resolves.toEqual([])
        expect(api.get).toHaveBeenCalledWith('/persons/7/rank-histories')
    })

    it('uses separate activation and update endpoints', async () => {
        vi.mocked(api.post).mockResolvedValue({ data: { personId: '7' } })
        vi.mocked(api.put).mockResolvedValue({ data: { personId: '7' } })

        await activatePersonRank('901', { date: '2024-06-15' })
        await updatePersonRankActivation('901', { date: null })

        expect(api.post).toHaveBeenCalledWith(
            '/person-rank-history/901/activation',
            { date: '2024-06-15' },
        )
        expect(api.put).toHaveBeenCalledWith(
            '/person-rank-history/901/activation',
            { date: null },
        )
    })
})

it('uses person-scoped manual source endpoints', async () => {
    vi.mocked(api.post).mockResolvedValue({ data: { id: 'source-1' } })
    vi.mocked(api.delete).mockResolvedValue({})
    const payload = {
        rankId: 6,
        achievedOn: '2026-01-01',
        activatedOn: '2026-01-10',
    }
    await addManualPersonRank('7', payload)
    await deleteManualPersonRank('7', 'source-1')
    expect(api.post).toHaveBeenCalledWith('/persons/7/manual-ranks', payload)
    expect(api.delete).toHaveBeenCalledWith('/persons/7/manual-ranks/source-1')
})
