import { describe, expect, it, vi } from 'vitest'
import { api } from './client'
import {
    deleteCompetition,
    getCompetition,
    updateCompetition,
} from './competitions'

vi.mock('./client', () => ({
    api: {
        get: vi.fn(),
        put: vi.fn(),
        delete: vi.fn(),
    },
}))

describe('competitions API', () => {
    it('loads one competition by its id', async () => {
        vi.mocked(api.get).mockResolvedValue({ data: { id: '42' } })

        await expect(getCompetition('42')).resolves.toEqual({ id: '42' })
        expect(api.get).toHaveBeenCalledWith('/competitions/42')
    })

    it('updates and deletes through the typed V1 resource paths', async () => {
        const payload = {
            name: 'Minsk Cup',
            description: 'Sprint',
            from: '2026-05-10',
            to: '2026-05-11',
            mass: false,
        }
        vi.mocked(api.put).mockResolvedValue({ data: { id: '42' } })
        vi.mocked(api.delete).mockResolvedValue({})

        await expect(updateCompetition('42', payload)).resolves.toEqual({
            id: '42',
        })
        await expect(deleteCompetition('42')).resolves.toBeUndefined()

        expect(api.put).toHaveBeenCalledWith('/competitions/42', payload)
        expect(api.delete).toHaveBeenCalledWith('/competitions/42')
    })
})
