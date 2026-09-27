// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import CupLayoutPage from './CupLayoutPage.vue'

const { getCup, route } = vi.hoisted(() => ({
    getCup: vi.fn(),
    route: { params: { cupId: '42', groupId: 'M_0_' } },
}))

vi.mock('../../api/cups', () => ({ getCup }))
vi.mock('../../api/users', () => ({ getUsers: vi.fn() }))
vi.mock('../../stores/auth', () => ({
    useAuthStore: () => ({ isAuthenticated: false }),
}))
vi.mock('vue-router', () => ({
    useRoute: () => route,
    useRouter: () => ({ replace: vi.fn() }),
}))

describe('cup layout page', () => {
    it('keeps the cup card and both tabs above the selected child page', async () => {
        getCup.mockResolvedValue({
            id: '42',
            name: 'Кубак',
            year: 2026,
            type: 'master',
            eventsCount: '1',
            groups: [{ id: 'M_0_', name: 'М0' }],
            visible: true,
        })

        const wrapper = mount(CupLayoutPage, {
            global: {
                stubs: {
                    Card: {
                        template:
                            '<section><slot name="title" /><slot name="content" /></section>',
                    },
                    CupTypeIcon: true,
                    ImpressionDetails: true,
                    ConfirmDeleteDialog: true,
                    Message: true,
                    RouterView: { template: '<div>Таблица загружается</div>' },
                    RouterLink: {
                        inheritAttrs: false,
                        props: ['to', 'custom', 'class'],
                        template:
                            '<slot :navigate="() => {}" :isActive="true" :isExactActive="true" />',
                    },
                    ActionButton: {
                        props: ['label'],
                        template: '<button>{{ label }}</button>',
                    },
                },
            },
        })
        await flushPromises()

        expect(wrapper.text()).toContain('Кубак')
        expect(wrapper.text()).toContain('Таблица загружается')
        expect(wrapper.text()).toContain('Этапы')
        expect(wrapper.text()).toContain('Таблица')
    })
})
