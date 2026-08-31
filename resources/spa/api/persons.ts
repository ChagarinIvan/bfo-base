import { api } from './client'
import type { PaginatedApiResponse, Person, PersonSearchQuery } from './types'

export async function getPersons(
    query: PersonSearchQuery = {},
): Promise<PaginatedApiResponse<Person>> {
    const response = await api.get<Person[]>('/persons', { params: query })

    return {
        data: response.data,
        headers: response.headers as Record<string, unknown>,
    }
}
