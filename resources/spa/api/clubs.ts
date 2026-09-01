import { api } from './client'
import type {
    Club,
    ClubSearchQuery,
    CreateClubRequest,
    PaginatedApiResponse,
} from './types'

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

export async function createClub(payload: CreateClubRequest): Promise<Club> {
    return (await api.post<Club>('/clubs', payload)).data
}

export async function updateClub(
    id: string,
    payload: CreateClubRequest,
): Promise<Club> {
    return (await api.put<Club>(`/clubs/${id}`, payload)).data
}
