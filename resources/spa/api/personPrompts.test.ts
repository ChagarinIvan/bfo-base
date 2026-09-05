import { beforeEach, describe, expect, it, vi } from 'vitest'
import { api } from './client'
import {
    createPersonPrompt,
    deletePersonPrompt,
    getPersonPrompts,
    updatePersonPrompt,
} from './personPrompts'

vi.mock('./client', () => ({
    api: {
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        delete: vi.fn(),
    },
}))

describe('person prompts api', () => {
    beforeEach(() => vi.clearAllMocks())

    it('loads a paginated person-filtered list', async () => {
        const headers = { 'x-pagination-total': '2' }
        vi.mocked(api.get).mockResolvedValue({ data: [{ id: '1' }], headers })

        await expect(
            getPersonPrompts({ personId: '62465', page: 2, perPage: 10 }),
        ).resolves.toEqual({ data: [{ id: '1' }], headers })
        expect(api.get).toHaveBeenCalledWith('/person-prompts', {
            params: { personId: '62465', page: 2, perPage: 10 },
        })
    })

    it('uses the prompt resource mutation endpoints', async () => {
        vi.mocked(api.post).mockResolvedValue({ data: { id: '1' } })
        vi.mocked(api.put).mockResolvedValue({ data: { id: '1' } })
        vi.mocked(api.delete).mockResolvedValue({})

        await createPersonPrompt('62465', { prompt: 'name' })
        await updatePersonPrompt('1', { prompt: 'updated' })
        await deletePersonPrompt('1')

        expect(api.post).toHaveBeenCalledWith('/persons/62465/prompts', {
            prompt: 'name',
        })
        expect(api.put).toHaveBeenCalledWith('/person-prompts/1', {
            prompt: 'updated',
        })
        expect(api.delete).toHaveBeenCalledWith('/person-prompts/1')
    })
})
