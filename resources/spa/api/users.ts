import { api } from './client'
import type { User } from './types'

const USERS_CACHE_KEY = 'spa_users_cache'
const USERS_CACHE_TTL_MS = 60 * 60 * 1000

interface UsersCache {
    expiresAt: number
    users: User[]
}

let memoryCache: UsersCache | null = null
let refreshRequest: Promise<User[]> | null = null

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

export async function getUsers(signal?: AbortSignal): Promise<User[]> {
    const now = Date.now()
    const cached = memoryCache ?? readStorageCache()
    if (cached && cached.expiresAt > now) {
        memoryCache = cached
        return cached.users
    }

    const users = (await api.get<User[]>('/users', { signal })).data
    const nextCache = {
        expiresAt: now + USERS_CACHE_TTL_MS,
        users,
    }
    memoryCache = nextCache
    saveStorageCache(nextCache)

    return users
}

export function refreshUsers(): Promise<User[]> {
    if (refreshRequest) return refreshRequest

    refreshRequest = api
        .get<User[]>('/users')
        .then(({ data: users }) => {
            const cache = {
                expiresAt: Date.now() + USERS_CACHE_TTL_MS,
                users,
            }
            memoryCache = cache
            saveStorageCache(cache)
            return users
        })
        .finally(() => {
            refreshRequest = null
        })

    return refreshRequest
}
