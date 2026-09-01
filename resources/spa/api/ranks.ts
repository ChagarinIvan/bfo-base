import { api } from './client'

export interface RankOption {
    id: string
    label: string
}

const RANKS_CACHE_KEY = 'spa_ranks_cache'
const RANKS_CACHE_TTL_MS = 60 * 60 * 1000
let memoryCache: { expiresAt: number; ranks: RankOption[] } | null = null

export function clearRanksCache(): void {
    memoryCache = null
    if (typeof localStorage !== 'undefined') {
        try {
            localStorage.removeItem(RANKS_CACHE_KEY)
        } catch {
            // Ignore storage cleanup failures.
        }
    }
}

export async function getRanks(): Promise<RankOption[]> {
    const now = Date.now()
    if (memoryCache && memoryCache.expiresAt > now) return memoryCache.ranks
    if (typeof localStorage !== 'undefined') {
        try {
            const stored = JSON.parse(
                localStorage.getItem(RANKS_CACHE_KEY) ?? 'null',
            ) as typeof memoryCache
            if (
                stored &&
                stored.expiresAt > now &&
                Array.isArray(stored.ranks)
            ) {
                memoryCache = stored
                return stored.ranks
            }
        } catch {
            // Ignore malformed storage data.
        }
    }

    const ranks = (await api.get<RankOption[]>('/ranks')).data
    memoryCache = { expiresAt: now + RANKS_CACHE_TTL_MS, ranks }

    try {
        if (typeof localStorage !== 'undefined') {
            localStorage.setItem(RANKS_CACHE_KEY, JSON.stringify(memoryCache))
        }
    } catch {
        // A disabled or full localStorage must not prevent loading the page.
    }

    return ranks
}
