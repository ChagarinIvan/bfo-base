import { api } from './client'
import type { Competition, UpdateCompetitionRequest } from './types'

export async function getCompetition(id: string): Promise<Competition> {
    return (await api.get<Competition>(`/competitions/${id}`)).data
}

export async function updateCompetition(
    id: string,
    payload: UpdateCompetitionRequest,
): Promise<Competition> {
    return (await api.put<Competition>(`/competitions/${id}`, payload)).data
}

export async function deleteCompetition(id: string): Promise<void> {
    await api.delete(`/competitions/${id}`)
}
