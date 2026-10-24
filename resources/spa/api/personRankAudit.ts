import { api } from './client'
import type { Impression } from './types'
import { paginationFromHeaders } from '../pages/listingModels'

export interface AppliedRank {
    rankId: number
    startedOn: string | null
    activatedOn: string | null
    finishedOn: string | null
    nextActivationOn: string | null
}
export type RankAuditTrigger =
    | 'manual'
    | 'scheduled'
    | 'protocol_import'
    | 'protocol_cleanup'
    | 'activation'
    | 'reassignment'
export interface RankAuditEntry {
    id: string
    personId: number
    trigger: RankAuditTrigger
    created: Impression
    applied: AppliedRank
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
