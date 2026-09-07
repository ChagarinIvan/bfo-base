import { api } from './client'
import type {
    PaginatedApiResponse,
    Person,
    PersonFormRequest,
    PersonSearchQuery,
} from './types'

export async function getPerson(
    id: string,
    refreshKey?: string,
): Promise<Person> {
    const response = refreshKey
        ? await api.get<Person>(`/persons/${id}`, {
              params: { refresh: refreshKey },
          })
        : await api.get<Person>(`/persons/${id}`)

    return response.data
}

export async function createPerson(value: PersonFormRequest): Promise<Person> {
    return (await api.post<Person>('/persons', value)).data
}

export async function updatePerson(
    id: string,
    value: PersonFormRequest,
): Promise<Person> {
    return (await api.put<Person>(`/persons/${id}`, value)).data
}

export async function getPersons(
    query: PersonSearchQuery = {},
): Promise<PaginatedApiResponse<Person>> {
    const response = await api.get<Person[]>('/persons', { params: query })

    return {
        data: response.data,
        headers: response.headers as Record<string, unknown>,
    }
}
