import { api } from './client'
import type { Event, PaginatedApiResponse } from './types'

export async function getCompetitionEvents(
    competitionId: string,
    page = 1,
    perPage = 20,
): Promise<PaginatedApiResponse<Event>> {
    const response = await api.get<Event[]>('/events', {
        params: { competitionId, page, perPage },
    })

    return {
        data: response.data,
        headers: response.headers as Record<string, unknown>,
    }
}
