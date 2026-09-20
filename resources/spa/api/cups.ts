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
    return api.get<CupEvent[]>(`/cups/${cupId}/events`, { params: query })
}

export async function getCupEvent(cupEventId: string) {
    return (await api.get<CupEvent>(`/cup-events/${cupEventId}`)).data
}

export async function createCupEvent(value: CreateCupEventRequest) {
    return (await api.post<CupEvent>('/cup-events', value)).data
}

export async function updateCupEvent(
    cupEventId: string,
    value: CupEventFormRequest,
) {
    return (
        await api.put<CupEvent>(`/cup-events/${cupEventId}`, value)
    ).data
}

export async function createCup(value: CreateCupRequest) {
    const response = await api.post<Cup>('/cups', value)
    return response.data
}

export async function updateCup(id: string, value: UpdateCupRequest) {
    const response = await api.put<Cup>(`/cups/${id}`, value)
    return response.data
}
