import { api } from './client'

const YEARS_CACHE_KEY = 'spa_years_cache'
const YEARS_CACHE_TTL_MS = 60 * 60 * 1000

interface YearsCache {
    expiresAt: number
    years: number[]
}

let memoryCache: YearsCache | null = null

function readStorageCache(): YearsCache | null {
    if (typeof localStorage === 'undefined') return null

    try {
        const value: unknown = JSON.parse(
            localStorage.getItem(YEARS_CACHE_KEY) ?? 'null',
        )
        if (
            typeof value !== 'object' ||
            value === null ||
            !('expiresAt' in value) ||
            !('years' in value) ||
            typeof value.expiresAt !== 'number' ||
            !Array.isArray(value.years) ||
            !value.years.every((year) => typeof year === 'number')
        ) {
            return null
        }

        return value as YearsCache
    } catch {
        return null
    }
}

function saveStorageCache(cache: YearsCache): void {
    if (typeof localStorage === 'undefined') return

    try {
        localStorage.setItem(YEARS_CACHE_KEY, JSON.stringify(cache))
    } catch {
        // A disabled or full localStorage must not prevent loading the page.
    }
}

export function clearYearsCache(): void {
    memoryCache = null
    if (typeof localStorage !== 'undefined') {
        try {
            localStorage.removeItem(YEARS_CACHE_KEY)
        } catch {
            // Ignore storage cleanup failures.
        }
    }
}

export async function getYears(): Promise<number[]> {
    const now = Date.now()
    const cached = memoryCache ?? readStorageCache()
    if (cached && cached.expiresAt > now) {
        memoryCache = cached
        return cached.years
    }

    const years = (await api.get<number[]>('/years')).data
    const nextCache = {
        expiresAt: now + YEARS_CACHE_TTL_MS,
        years,
    }
    memoryCache = nextCache
    saveStorageCache(nextCache)

    return years
}
