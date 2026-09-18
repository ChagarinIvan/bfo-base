// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import CupsPage from './CupsPage.vue'

const { getCups, getYears, auth } = vi.hoisted(() => ({
    getCups: vi.fn(),
    getYears: vi.fn(),
    auth: {
        isAuthenticated: false,
        setAuthenticated: (() => undefined) as (value: boolean) => void,
    },
}))

vi.mock('../../api/cups', () => ({ getCups }))
vi.mock('../../api/years', () => ({ getYears }))
vi.mock('vue-router', () => ({ useRouter: () => ({ push: vi.fn() }) }))
vi.mock('../../stores/auth', async () => {
    const { reactive } = await import('vue')
    const store = reactive(auth)
    auth.setAuthenticated = (value: boolean) => {
        store.isAuthenticated = value
    }

    return { useAuthStore: () => store }
})

function mountPage() {
    return mount(CupsPage, {
        global: {
            stubs: {
                Button: true,
                FilterPanel: { template: '<div><slot /></div>' },
                ImpressionDetails: true,
                InputText: true,
                ListingTable: {
                    props: [
                        'columns',
                        'items',
                        'pagination',
                        'loading',
                        'error',
                    ],
                    template: `
                        <div>
                            <div data-testid="columns">{{ columns.map((column) => column.key).join(',') }}</div>
                            <div data-testid="items">{{ items.length }}</div>
                            <slot name="filters" />
                        </div>
                    `,
                },
                Select: {
                    props: ['modelValue'],
                    template:
                        '<button data-testid="visible-all" @click="$emit(\'update:modelValue\', \'all\')">all</button>',
                },
                Toolbar: {
                    template:
                        '<div><slot name="start" /><slot name="end" /></div>',
                },
                YearFilter: true,
            },
        },
    })
}

describe('cups page', () => {
    beforeEach(() => {
        auth.setAuthenticated(false)
        vi.resetAllMocks()
        getYears.mockResolvedValue([2026])
        getCups.mockResolvedValue({
            data: [
                {
                    id: '1',
                    name: 'Master Cup',
                    eventsCount: '4',
                    year: 2026,
                    type: 'master',
                    groups: [],
                    visible: true,
                },
            ],
            headers: {},
        })
    })

    it('loads the public listing and exposes the events count column', async () => {
        const wrapper = mountPage()
        await flushPromises()

        expect(getCups).toHaveBeenCalledWith({
            year: 2026,
            page: 1,
            perPage: 20,
            visible: '1',
        })
        expect(wrapper.get('[data-testid="items"]').text()).toBe('1')
        expect(wrapper.get('[data-testid="columns"]').text()).toContain(
            'eventsCount',
        )
    })

    it('resets the visibility filter and reloads public data after logout', async () => {
        auth.setAuthenticated(true)
        const wrapper = mountPage()
        await flushPromises()

        await wrapper.get('[data-testid="visible-all"]').trigger('click')
        await flushPromises()
        expect(getCups).toHaveBeenLastCalledWith({
            year: 2026,
            page: 1,
            perPage: 20,
        })

        auth.setAuthenticated(false)
        await flushPromises()
        expect(getCups).toHaveBeenLastCalledWith({
            year: 2026,
            page: 1,
            perPage: 20,
            visible: '1',
        })
    })
})
