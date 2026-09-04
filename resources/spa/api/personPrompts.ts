import { api } from './client'
import type {
    PaginatedApiResponse,
    PersonPrompt,
    PersonPromptRequest,
    PersonPromptSearchQuery,
} from './types'

export async function getPersonPrompts(
    query: PersonPromptSearchQuery,
): Promise<PaginatedApiResponse<PersonPrompt>> {
    const response = await api.get<PersonPrompt[]>('/person-prompts', {
        params: query,
    })
    return {
        data: response.data,
        headers: response.headers as Record<string, unknown>,
    }
}

export async function getPersonPrompt(id: string): Promise<PersonPrompt> {
    return (await api.get<PersonPrompt>(`/person-prompts/${id}`)).data
}

export async function createPersonPrompt(
    personId: string,
    payload: PersonPromptRequest,
): Promise<PersonPrompt> {
    return (
        await api.post<PersonPrompt>(`/persons/${personId}/prompts`, payload)
    ).data
}

export async function updatePersonPrompt(
    id: string,
    payload: PersonPromptRequest,
): Promise<PersonPrompt> {
    return (await api.put<PersonPrompt>(`/person-prompts/${id}`, payload)).data
}

export async function deletePersonPrompt(id: string): Promise<void> {
    await api.delete(`/person-prompts/${id}`)
}
