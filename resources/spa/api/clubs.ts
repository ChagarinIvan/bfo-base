import { api } from './client'
import type {
    Club,
    ClubOption,
    ClubSearchQuery,
    CreateClubRequest,
    PaginatedApiResponse,
} from './types'

const CLUB_OPTIONS_CACHE_TTL_MS = 60 * 60 * 1000
const CLUB_OPTIONS_CACHE_KEY = 'spa_club_options_cache'
let clubOptionsMemoryCache: { expiresAt: number; clubs: ClubOption[] } | null =
    null

function isClubOptionsCache(
    value: unknown,
): value is { expiresAt: number; clubs: ClubOption[] } {
    return (
        typeof value === 'object' &&
        value !== null &&
        'expiresAt' in value &&
        typeof value.expiresAt === 'number' &&
        'clubs' in value &&
        Array.isArray(value.clubs) &&
        value.clubs.every(
            (club): club is ClubOption =>
                typeof club === 'object' &&
                club !== null &&
                'id' in club &&
                typeof club.id === 'string' &&
                'name' in club &&
                typeof club.name === 'string',
        )
    )
}

export async function getClubs(
    query: ClubSearchQuery = {},
): Promise<PaginatedApiResponse<Club>> {
    const response = await api.get<Club[]>('/clubs', { params: query })

    return {
        data: response.data,
        headers: response.headers as Record<string, unknown>,
    }
}

export async function getClub(id: string): Promise<Club> {
    return (await api.get<Club>(`/clubs/${id}`)).data
}

export function clearClubOptionsCache(): void {
    clubOptionsMemoryCache = null
    if (typeof localStorage !== 'undefined') {
        try {
            localStorage.removeItem(CLUB_OPTIONS_CACHE_KEY)
        } catch {
            // Ignore storage cleanup failures.
        }
    }
}

export async function getClubOptions(): Promise<ClubOption[]> {
    const now = Date.now()
    if (clubOptionsMemoryCache && clubOptionsMemoryCache.expiresAt > now) {
        return clubOptionsMemoryCache.clubs
    }

    if (typeof localStorage !== 'undefined') {
        try {
            const stored = JSON.parse(
                localStorage.getItem(CLUB_OPTIONS_CACHE_KEY) ?? 'null',
            ) as unknown
            if (isClubOptionsCache(stored) && stored.expiresAt > now) {
                clubOptionsMemoryCache = stored
                return stored.clubs
            }
        } catch {
            // Ignore malformed storage data.
        }
    }

    const clubs = (await api.get<ClubOption[]>('/clubs/all')).data
    clubOptionsMemoryCache = {
        expiresAt: now + CLUB_OPTIONS_CACHE_TTL_MS,
        clubs,
    }

    try {
        if (typeof localStorage !== 'undefined') {
            localStorage.setItem(
                CLUB_OPTIONS_CACHE_KEY,
                JSON.stringify(clubOptionsMemoryCache),
            )
        }
    } catch {
        // A disabled or full localStorage must not prevent loading the page.
    }

    return clubs
}

export async function createClub(payload: CreateClubRequest): Promise<Club> {
    return (await api.post<Club>('/clubs', payload)).data
}

export async function updateClub(
    id: string,
    payload: CreateClubRequest,
): Promise<Club> {
    return (await api.put<Club>(`/clubs/${id}`, payload)).data
}
