// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import PersonPaymentsPage from './PersonPaymentsPage.vue'

const { getPersonPayments, getYears, push } = vi.hoisted(() => ({
    getPersonPayments: vi.fn(),
    getYears: vi.fn(),
    push: vi.fn(),
}))

vi.mock('../../api/personPayments', () => ({ getPersonPayments }))
vi.mock('../../api/years', () => ({ getYears }))
vi.mock('../../stores/auth', () => ({
    useAuthStore: () => ({ isAuthenticated: true }),
}))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { personId: '7' } }),
    useRouter: () => ({ push }),
}))

describe('person payments page', () => {
    beforeEach(() => vi.resetAllMocks())

    it('loads the list and exposes the authenticated create action', async () => {
        getYears.mockResolvedValue([2025, 2024])
        getPersonPayments.mockResolvedValue({
            data: [
                {
                    id: '1',
                    personId: '7',
                    year: '2025',
                    date: '2025-01-01',
                },
            ],
            headers: { 'x-pagination-total': '1' },
        })

        const wrapper = mount(PersonPaymentsPage, {
            global: {
                stubs: {
                    Button: {
                        props: ['label'],
                        template: '<button>{{ label }}</button>',
                    },
                    Column: true,
                    DataTable: { template: '<div><slot /></div>' },
                    Message: { template: '<div><slot /></div>' },
                    Paginator: true,
                    YearFilter: true,
                },
            },
        })
        await flushPromises()

        expect(getPersonPayments).toHaveBeenCalledWith({
            personId: '7',
            year: undefined,
            page: 1,
            perPage: 20,
        })
        expect(wrapper.text()).toContain('Аплаты персоны')
        expect(wrapper.text()).not.toContain('Аплаты не знойдзены.')
    })

    it('shows an empty state', async () => {
        getYears.mockResolvedValue([2025, 2024])
        getPersonPayments.mockResolvedValue({ data: [], headers: {} })

        const wrapper = mount(PersonPaymentsPage, {
            global: {
                stubs: {
                    Button: true,
                    Column: true,
                    DataTable: true,
                    Message: { template: '<div><slot /></div>' },
                    Paginator: true,
                    YearFilter: true,
                },
            },
        })
        await flushPromises()

        expect(wrapper.text()).toContain('Аплаты не знойдзены.')
    })
})
