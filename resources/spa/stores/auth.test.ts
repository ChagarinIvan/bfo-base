import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'

const post = vi.fn()
const del = vi.fn()
const setBearerToken = vi.fn()
const setUnauthorizedHandler = vi.fn()

vi.mock('../api/client', () => ({
    api: { post, delete: del },
    setBearerToken,
    setUnauthorizedHandler,
}))

class MemoryStorage implements Storage {
    private values = new Map<string, string>()

    get length(): number {
        return this.values.size
    }

    clear(): void {
        this.values.clear()
    }

    getItem(key: string): string | null {
        return this.values.get(key) ?? null
    }

    key(index: number): string | null {
        return [...this.values.keys()][index] ?? null
    }

    removeItem(key: string): void {
        this.values.delete(key)
    }

    setItem(key: string, value: string): void {
        this.values.set(key, value)
    }
}

Object.defineProperty(globalThis, 'localStorage', {
    value: new MemoryStorage(),
    configurable: true,
})

const { useAuthStore } = await import('./auth')

describe('auth store', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
        localStorage.clear()
        post.mockReset()
        del.mockReset()
        setBearerToken.mockReset()
        setUnauthorizedHandler.mockReset()
    })

    it('stores the login token and attaches the bearer header', async () => {
        post.mockResolvedValue({
            data: { token: '1|token', token_type: 'Bearer' },
        })
        const store = useAuthStore()

        await store.login('user@example.com', 'secret')

        expect(store.token).toBe('1|token')
        expect(localStorage.getItem('auth_token')).toBe('1|token')
        expect(setBearerToken).toHaveBeenCalledWith('1|token')
    })

    it('clears the token after logout even when the request succeeds', async () => {
        del.mockResolvedValue({})
        localStorage.setItem('auth_token', '1|token')
        const store = useAuthStore()
        await store.hydrate()

        await store.logout()

        expect(store.token).toBeNull()
        expect(localStorage.getItem('auth_token')).toBeNull()
        expect(del).toHaveBeenCalledWith('/auth/logout')
    })
})
