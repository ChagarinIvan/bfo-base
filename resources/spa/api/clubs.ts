import { api } from './client'
import type { Club, ClubSearchQuery, PaginatedApiResponse } from './types'

export async function getClubs(
    query: ClubSearchQuery = {},
): Promise<PaginatedApiResponse<Club>> {
    const response = await api.get<Club[]>('/clubs', { params: query })

    return {
        data: response.data,
        headers: response.headers as Record<string, unknown>,
    }
}

export async function getClub(id: string): Promise<Club> {
    return (await api.get<Club>(`/clubs/${id}`)).data
}
