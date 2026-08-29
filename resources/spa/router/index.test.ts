import { createMemoryHistory } from 'vue-router'
import { beforeEach, describe, expect, it } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'

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

const { createAppRouter } = await import('./index')

describe('SPA navigation guard', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
        localStorage.clear()
    })

    it('redirects an unauthenticated user to login', async () => {
        const router = createAppRouter(createMemoryHistory())

        await router.push('/app/competitions/create')

        expect(router.currentRoute.value.path).toBe('/app/login')
        expect(router.currentRoute.value.query.return).toBe(
            '/app/competitions/create',
        )
    })
})
