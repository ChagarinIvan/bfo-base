import { api } from './client'
import type { Cup, CupSearchQuery } from './types'

export async function getCups(query: CupSearchQuery) {
    return api.get<Cup[]>('/cups', { params: query })
}
