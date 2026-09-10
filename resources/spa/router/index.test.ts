import { createMemoryHistory } from 'vue-router'
import { beforeEach, describe, expect, it } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import {
    authenticatedAccountNavigation,
    authenticatedCompetitionNavigation,
    competitionNavigation,
    personsNavigation,
} from '../components/navigationModels'

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

    it('resolves the public competition details route', async () => {
        const router = createAppRouter(createMemoryHistory())

        await router.push('/app/competitions/42')

        expect(router.currentRoute.value.path).toBe('/app/competitions/42')
    })

    it('resolves the public person details route', async () => {
        const router = createAppRouter(createMemoryHistory())

        await router.push('/app/persons/42')

        expect(router.currentRoute.value.path).toBe('/app/persons/42')
    })

    it('shows an SPA not-found route for an unknown application path', async () => {
        const router = createAppRouter(createMemoryHistory())

        await router.push('/app/missing')

        expect(router.currentRoute.value.path).toBe('/app/missing')
    })

    it('protects registration and keeps activation public', async () => {
        const router = createAppRouter(createMemoryHistory())

        await router.push('/app/registration')
        expect(router.currentRoute.value.path).toBe('/app/login')

        await router.push('/app/registration/activate/token')
        expect(router.currentRoute.value.path).toBe(
            '/app/registration/activate/token',
        )
    })

    it('keeps the person info layout while switching person tabs', async () => {
        localStorage.setItem('auth_token', 'test-token')
        const router = createAppRouter(createMemoryHistory())

        await router.push('/app/persons/42')
        expect(router.currentRoute.value.matched[0]?.path).toBe(
            '/app/persons/:personId',
        )

        await router.push('/app/persons/42/payments')
        expect(router.currentRoute.value.matched[0]?.path).toBe(
            '/app/persons/:personId',
        )
        expect(router.currentRoute.value.matched[1]?.path).toBe(
            '/app/persons/:personId/payments',
        )

        await router.push('/app/persons/42/ranks')
        expect(router.currentRoute.value.matched[0]?.path).toBe(
            '/app/persons/:personId',
        )
        expect(router.currentRoute.value.matched[1]?.path).toBe(
            '/app/persons/:personId/ranks',
        )
    })

    it('resolves the public clubs listing route', async () => {
        const router = createAppRouter(createMemoryHistory())

        await router.push('/app/clubs')

        expect(router.currentRoute.value.path).toBe('/app/clubs')
    })

    it('resolves the public club details route', async () => {
        const router = createAppRouter(createMemoryHistory())

        await router.push('/app/clubs/42')

        expect(router.currentRoute.value.path).toBe('/app/clubs/42')
    })

    it('protects the competition edit route', async () => {
        const router = createAppRouter(createMemoryHistory())

        await router.push('/app/competitions/42/edit')

        expect(router.currentRoute.value.path).toBe('/app/login')
        expect(router.currentRoute.value.query.return).toBe(
            '/app/competitions/42/edit',
        )
    })

    it('protects club create and edit routes', async () => {
        const router = createAppRouter(createMemoryHistory())

        await router.push('/app/clubs/create')
        expect(router.currentRoute.value.path).toBe('/app/login')

        await router.push('/app/clubs/42/edit')
        expect(router.currentRoute.value.path).toBe('/app/login')
    })

    it('protects person payment list and create routes', async () => {
        const router = createAppRouter(createMemoryHistory())

        await router.push('/app/persons/42/payments')
        expect(router.currentRoute.value.path).toBe('/app/login')
        expect(router.currentRoute.value.query.return).toBe(
            '/app/persons/42/payments',
        )

        await router.push('/app/persons/42/payments/create')
        expect(router.currentRoute.value.path).toBe('/app/login')
    })

    it('protects the standalone person edit route', async () => {
        const router = createAppRouter(createMemoryHistory())

        await router.push('/app/persons/42/edit')

        expect(router.currentRoute.value.path).toBe('/app/login')
        expect(router.currentRoute.value.query.return).toBe(
            '/app/persons/42/edit',
        )
    })

    it('keeps legacy navigation destinations outside the SPA router', () => {
        const router = createAppRouter(createMemoryHistory())
        const registeredPaths = router.getRoutes().map((route) => route.path)

        expect(registeredPaths).toContain(competitionNavigation[0].href)

        for (const item of [
            ...competitionNavigation.slice(1),
            ...personsNavigation,
            ...authenticatedCompetitionNavigation,
            ...authenticatedAccountNavigation,
        ]) {
            if (item.spa) {
                expect(registeredPaths).toContain(item.href)
            } else {
                expect(registeredPaths).not.toContain(item.href)
            }
        }
    })
})
