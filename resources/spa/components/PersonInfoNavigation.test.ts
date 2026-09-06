// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { createMemoryHistory, createRouter, type Router } from 'vue-router'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import PersonInfoNavigation from './PersonInfoNavigation.vue'

const { auth } = vi.hoisted(() => ({ auth: { isAuthenticated: true } }))

vi.mock('../stores/auth', () => ({
    useAuthStore: () => auth,
}))

function router(): Router {
    return createRouter({
        history: createMemoryHistory(),
        routes: [
            { path: '/app/persons/:personId', component: {} },
            { path: '/app/persons/:personId/prompts', component: {} },
            { path: '/app/persons/:personId/payments', component: {} },
        ],
    })
}

describe('person info navigation', () => {
    beforeEach(() => {
        auth.isAuthenticated = true
    })

    it('keeps the five person destinations in one navigation block', async () => {
        const appRouter = router()
        await appRouter.push('/app/persons/7')

        const wrapper = mount(PersonInfoNavigation, {
            props: { personId: '7' },
            global: {
                plugins: [appRouter],
                stubs: {
                    Button: {
                        emits: ['click'],
                        props: ['label', 'icon', 'text'],
                        template:
                            '<button :data-icon="icon" :data-text="text" @click="$emit(\'click\')">{{ label }}</button>',
                    },
                },
            },
        })

        expect(wrapper.findAll('button')).toHaveLength(5)
        expect(
            wrapper.findAll('button')[0].attributes('data-text'),
        ).toBeDefined()
        expect(wrapper.findAll('button')[1].attributes('data-icon')).toBe(
            'pi pi-comments',
        )

        await wrapper.findAll('button')[1].trigger('click')
        await flushPromises()
        expect(appRouter.currentRoute.value.path).toBe('/app/persons/7/prompts')

        await wrapper.findAll('button')[2].trigger('click')
        await flushPromises()
        expect(appRouter.currentRoute.value.path).toBe(
            '/app/persons/7/payments',
        )

        await wrapper.findAll('button')[4].trigger('click')
        await flushPromises()
        expect(appRouter.currentRoute.value.path).toBe('/app/persons/7')
    })
})
