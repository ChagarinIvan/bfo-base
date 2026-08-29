import { api } from './client'
import type { User } from './types'

const USERS_CACHE_KEY = 'spa_users_cache'
const USERS_CACHE_TTL_MS = 60 * 60 * 1000

interface UsersCache {
    expiresAt: number
    users: User[]
}

let memoryCache: UsersCache | null = null

function readStorageCache(): UsersCache | null {
    if (typeof localStorage === 'undefined') return null

    try {
        const value: unknown = JSON.parse(
            localStorage.getItem(USERS_CACHE_KEY) ?? 'null',
        )
        if (
            typeof value !== 'object' ||
            value === null ||
            !('expiresAt' in value) ||
            !('users' in value) ||
            typeof value.expiresAt !== 'number' ||
            !Array.isArray(value.users)
        ) {
            return null
        }

        return value as UsersCache
    } catch {
        return null
    }
}

function saveStorageCache(cache: UsersCache): void {
    if (typeof localStorage === 'undefined') return

    try {
        localStorage.setItem(USERS_CACHE_KEY, JSON.stringify(cache))
    } catch {
        // A disabled or full localStorage must not prevent loading the page.
    }
}

export function clearUsersCache(): void {
    memoryCache = null
    if (typeof localStorage !== 'undefined') {
        try {
            localStorage.removeItem(USERS_CACHE_KEY)
        } catch {
            // Ignore storage cleanup failures.
        }
    }
}

export async function getUsers(): Promise<User[]> {
    const now = Date.now()
    const cached = memoryCache ?? readStorageCache()
    if (cached && cached.expiresAt > now) {
        memoryCache = cached
        return cached.users
    }

    const users = (await api.get<User[]>('/users')).data
    const nextCache = {
        expiresAt: now + USERS_CACHE_TTL_MS,
        users,
    }
    memoryCache = nextCache
    saveStorageCache(nextCache)

    return users
}
