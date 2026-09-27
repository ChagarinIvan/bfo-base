// @vitest-environment happy-dom

import { beforeEach, describe, expect, it } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { createMemoryHistory } from 'vue-router'
import { createAppRouter } from './router'
import { appearanceNightClass, useAppearanceStore } from './stores/appearance'

describe('SPA appearance route persistence', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
        localStorage.clear()
        document.documentElement.classList.remove(appearanceNightClass)
    })

    it('keeps night mode while moving across covered public routes', async () => {
        const router = createAppRouter(createMemoryHistory())
        const appearance = useAppearanceStore()
        appearance.setMode('night')

        for (const path of ['/app/competitions', '/app/cups', '/app/persons']) {
            await router.push(path)
            expect(router.currentRoute.value.path).toBe(path)
            expect(appearance.mode).toBe('night')
            expect(
                document.documentElement.classList.contains(
                    appearanceNightClass,
                ),
            ).toBe(true)
        }
    })
})
