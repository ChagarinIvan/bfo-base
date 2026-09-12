import { api } from './client'
import type { Distance } from './types'

export async function getEventDistances(eventId: string): Promise<Distance[]> {
    return (await api.get<Distance[]>('/distances', { params: { eventId } }))
        .data
}
