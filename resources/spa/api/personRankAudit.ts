import { api } from './client'
import type { Impression } from './types'
import { paginationFromHeaders } from '../pages/listingModels'

export interface AuditRank {
    rankId: number
    startedOn: string | null
    activatedOn: string | null
    finishedOn: string | null
}
export interface AuditHistory extends AuditRank {
    protocolLineId: number
    eventId: number
    competitionId: number
    distanceId: number
    achievedOn: string
    changeType: string
}
export interface AuditSnapshot {
    current: AuditRank
    history: AuditHistory[]
    nextActivationOn: string | null
}
export type RankAuditTrigger =
    | 'manual'
    | 'queued'
    | 'scheduled'
    | 'refill'
    | 'protocol_import'
    | 'protocol_cleanup'
    | 'activation'
    | 'reassignment'
    | 'correction'
export interface RankAuditEntry {
    id: string
    personId: number
    trigger: RankAuditTrigger
    created: Impression
    before: AuditSnapshot | null
    after: AuditSnapshot | null
}

export async function getRankAudit(personId: string, page = 1, perPage = 20) {
    const response = await api.get<RankAuditEntry[]>(
        `/persons/${personId}/rank-audit`,
        { params: { page, perPage } },
    )
    return {
        data: response.data,
        pagination: paginationFromHeaders(
            response.headers as Record<string, unknown>,
        ),
    }
}
