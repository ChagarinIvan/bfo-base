import { createMemoryHistory, createRouter } from 'vue-router'
import { describe, expect, it } from 'vitest'
import CompetitionDetailsPage from './CompetitionDetailsPage.vue'

describe('competition details page', () => {
    it('is the component rendered by the public SPA detail route', () => {
        const router = createRouter({
            history: createMemoryHistory(),
            routes: [
                {
                    path: '/app/competitions/:id',
                    component: CompetitionDetailsPage,
                },
            ],
        })

        expect(
            router.resolve('/app/competitions/42').matched[0]?.components
                ?.default,
        ).toBe(CompetitionDetailsPage)
    })
})
