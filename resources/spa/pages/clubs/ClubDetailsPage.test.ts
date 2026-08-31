import { createMemoryHistory, createRouter } from 'vue-router'
import { describe, expect, it } from 'vitest'
import ClubDetailsPage from './ClubDetailsPage.vue'

describe('club details page', () => {
    it('is the component rendered by the public SPA detail route', () => {
        const router = createRouter({
            history: createMemoryHistory(),
            routes: [{ path: '/app/clubs/:id', component: ClubDetailsPage }],
        })

        expect(
            router.resolve('/app/clubs/42').matched[0]?.components?.default,
        ).toBe(ClubDetailsPage)
    })
})
