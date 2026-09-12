import { api } from './client'
import type {
    Event,
    EventFormRequest,
    GroupEventsQuery,
    PaginatedApiResponse,
} from './types'

function eventFormData(payload: EventFormRequest): FormData {
    const data = new FormData()
    data.set('name', payload.name)
    data.set('description', payload.description)
    data.set('date', payload.date)
    if (payload.protocol) data.set('protocol', payload.protocol)
    if (payload.url) data.set('url', payload.url)

    return data
}

export async function createEvent(
    competitionId: string,
    payload: EventFormRequest,
): Promise<Event> {
    return (
        await api.post<Event>(
            `/competitions/${competitionId}/events`,
            eventFormData(payload),
        )
    ).data
}

export async function updateEvent(
    eventId: string,
    payload: EventFormRequest,
): Promise<Event> {
    return (await api.put<Event>(`/events/${eventId}`, eventFormData(payload)))
        .data
}

export async function deleteEvent(eventId: string): Promise<void> {
    await api.delete(`/events/${eventId}`)
}

export async function uniteEvents(
    competitionId: string,
    eventIds: string[],
): Promise<Event> {
    return (
        await api.post<Event>(`/competitions/${competitionId}/events/unite`, {
            eventIds,
        })
    ).data
}

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
