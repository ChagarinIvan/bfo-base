import { api } from './client'
import type { Impression } from './types'
import { paginationFromHeaders } from '../pages/listingModels'

export interface RankCheckRow {
    position: number
    group: string | null
    name: string
    club: string | null
    rank: string | null
    number: string | null
    year: string | null
    personId: string | null
    databaseName: string | null
    databaseClub: string | null
    databaseRank: string | null
    databaseYear: string | null
    hasPerson: boolean
    isEqual: boolean
}

export interface RankCheck {
    id: string
    status: 'PARSING' | 'READY' | 'FAILED'
    created: Impression
    updated: Impression
    error?: string
}

export async function createRankCheck(
    file: File,
): Promise<Pick<RankCheck, 'id' | 'status' | 'updated'>> {
    const form = new FormData()
    form.append('list', file)
    return (await api.post('/rank-checks', form)).data
}

export async function getRankChecks(
    page = 1,
    perPage = 20,
): Promise<{ data: RankCheck[]; headers: Record<string, unknown> }> {
    const response = await api.get<RankCheck[]>('/rank-checks', {
        params: { page, perPage },
    })

    return {
        data: response.data,
        headers: response.headers as Record<string, unknown>,
    }
}

export async function getRankCheck(id: string): Promise<RankCheck> {
    return (await api.get<RankCheck>(`/rank-checks/${id}`)).data
}

export async function listRankCheckRows(
    id: string,
    page = 1,
): Promise<{ data: RankCheckRow[]; lastPage: number }> {
    const response = await api.get<RankCheckRow[]>(`/rank-checks/${id}/rows`, {
        params: { page, perPage: 50 },
    })

    return {
        data: response.data,
        lastPage: paginationFromHeaders(
            response.headers as Record<string, unknown>,
        ).lastPage,
    }
}
