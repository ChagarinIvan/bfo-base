import { api } from './client'
import type {
    Cup,
    CupEvent,
    CupEventFormRequest,
    CreateCupEventRequest,
    CupEventSearchQuery,
    CupSearchQuery,
    CreateCupRequest,
    UpdateCupRequest,
} from './types'

export async function getCups(query: CupSearchQuery) {
    return api.get<Cup[]>('/cups', { params: query })
}

export async function getCup(id: string) {
    const response = await api.get<Cup>(`/cups/${id}`)
    return response.data
}

export async function getCupEvents(cupId: string, query: CupEventSearchQuery) {
    return api.get<CupEvent[]>('/cup-events', {
        params: { ...query, cupId },
    })
}

export async function getCupEvent(cupEventId: string) {
    return (await api.get<CupEvent>(`/cup-events/${cupEventId}`)).data
}

export async function getCupEventContexts(eventIds: string[]) {
    if (eventIds.length === 0) return []

    const cupEvents = (
        await api.get<CupEvent[]>('/cup-events', {
            params: { eventIds, perPage: Math.min(eventIds.length, 1000) },
        })
    ).data
    const cups = (
        await getCups({
            ids: [...new Set(cupEvents.map((cupEvent) => cupEvent.cupId))],
            perPage: 100,
        })
    ).data
    const cupsById = Object.fromEntries(cups.map((cup) => [cup.id, cup]))

    return cupEvents.flatMap((cupEvent) => {
        const cup = cupsById[cupEvent.cupId]
        if (!cup) return []

        return {
            eventId: cupEvent.eventId,
            cupEventId: cupEvent.id,
            cupId: cup.id,
            cupName: cup.name,
            cupType: cup.type,
            href: '/app/cups/' + cup.id,
        }
    })
}

export async function createCupEvent(value: CreateCupEventRequest) {
    return (await api.post<CupEvent>('/cup-events', value)).data
}

export async function updateCupEvent(
    cupEventId: string,
    value: CupEventFormRequest,
) {
    return (await api.put<CupEvent>(`/cup-events/${cupEventId}`, value)).data
}

export async function createCup(value: CreateCupRequest) {
    const response = await api.post<Cup>('/cups', value)
    return response.data
}

export async function updateCup(id: string, value: UpdateCupRequest) {
    const response = await api.put<Cup>(`/cups/${id}`, value)
    return response.data
}
