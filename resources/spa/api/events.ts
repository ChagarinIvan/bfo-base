import { api } from './client'
import type { Event, GroupEventsQuery, PaginatedApiResponse } from './types'

export async function getEvent(id: string): Promise<Event> {
    return (await api.get<Event>(`/events/${id}`)).data
}

export async function getEventsByIds(ids: string[]): Promise<Event[]> {
    if (!ids.length) return []

    const response = await api.get<Event[]>('/events', {
        params: {
            ids,
            withCompetition: 1,
            perPage: ids.length,
        },
    })

    return response.data
}

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

export async function getGroupEvents(
    query: GroupEventsQuery,
): Promise<PaginatedApiResponse<Event>> {
    const response = await api.get<Event[]>('/events', { params: query })

    return {
        data: response.data,
        headers: response.headers as Record<string, unknown>,
    }
}
