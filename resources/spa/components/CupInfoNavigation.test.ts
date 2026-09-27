// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import { defineComponent, h } from 'vue'
import { describe, expect, it, vi } from 'vitest'
import CupInfoNavigation from './CupInfoNavigation.vue'

vi.mock('../stores/auth', () => ({
    useAuthStore: () => ({ isAuthenticated: true }),
}))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { groupId: 'M_0_' }, query: {} }),
}))

describe('cup info navigation', () => {
    it('keeps edit, event, tab, export, cache, delete actions in order', () => {
        const RouterLink = defineComponent({
            props: {
                to: { type: [String, Object], required: true },
                custom: { type: Boolean, default: false },
            },
            setup(_, { slots }) {
                return () =>
                    h(
                        'span',
                        slots.default?.({
                            navigate: () => undefined,
                            isActive: false,
                            isExactActive: false,
                        }),
                    )
            },
        })
        const wrapper = mount(CupInfoNavigation, {
            props: { cupId: '42', firstGroupId: 'M_0_' },
            global: {
                components: { RouterLink },
                stubs: {
                    ActionButton: {
                        props: ['label'],
                        template: '<button>{{ label }}</button>',
                    },
                },
            },
        })

        const labels = wrapper.findAll('button').map((button) => button.text())

        expect(labels).toEqual([
            'Рэдагаваць',
            'Дадаць этап',
            'Этапы',
            'Табліца',
            'Экспарт',
            'Абнавіць кэш',
            'Выдаліць',
        ])
    })
})
