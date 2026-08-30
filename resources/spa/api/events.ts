import { api } from './client'
import type { Event } from './types'

export async function getCompetitionEvents(
    competitionId: string,
): Promise<Event[]> {
    return (
        await api.get<Event[]>('/events', {
            params: { competitionId },
        })
    ).data
}
