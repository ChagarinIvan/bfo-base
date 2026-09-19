// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { afterEach, describe, expect, it, vi } from 'vitest'
import CupViewPage from './CupViewPage.vue'

const { auth, getCup, getCupEvents, getEventsByIds, getUsers, route } =
    vi.hoisted(() => ({
        auth: { isAuthenticated: false },
        getCup: vi.fn(),
        getCupEvents: vi.fn(),
        getEventsByIds: vi.fn(),
        getUsers: vi.fn(),
        route: { params: { cupId: '42' } },
    }))

vi.mock('../../api/cups', () => ({ getCup, getCupEvents }))
vi.mock('../../api/events', () => ({ getEventsByIds }))
vi.mock('../../api/users', () => ({ getUsers }))
vi.mock('../../stores/auth', () => ({ useAuthStore: () => auth }))
vi.mock('vue-router', () => ({
    RouterLink: { props: ['to'], template: '<a :href="to"><slot /></a>' },
    useRoute: () => route,
    useRouter: () => ({ replace: vi.fn() }),
}))

function mountPage() {
    return mount(CupViewPage, {
        global: {
            stubs: {
                Card: {
                    template:
                        '<section><slot name="title" /><slot name="content" /></section>',
                },
                Message: true,
                InputText: true,
                FilterPanel: { template: '<div><slot /></div>' },
                DateFilter: true,
                ImpressionDetails: true,
                ConfirmDeleteDialog: true,
                RouterLink: {
                    props: ['to'],
                    template: '<a :href="to"><slot /></a>',
                },
                ListingTable: {
                    props: ['columns', 'items'],
                    template:
                        '<div data-testid="columns">{{ columns.map((item) => item.key).join(",") }}<slot name="cell-actions" :data="items[0]" /></div>',
                },
            },
        },
    })
}

describe('cup view page', () => {
    afterEach(() => {
        auth.isAuthenticated = false
        vi.resetAllMocks()
    })

    it('renders a public card and compact stage request without admin controls', async () => {
        getCup.mockResolvedValue({
            id: '42',
            name: 'Кубак',
            year: 2026,
            type: 'master',
            eventsCount: '1',
            groups: [],
            visible: true,
        })
        getCupEvents.mockResolvedValue({
            data: [{ id: '7', cupId: '42', eventId: '9', points: '10' }],
            headers: {},
        })
        getEventsByIds.mockResolvedValue([
            {
                id: '9',
                name: 'Этап',
                competitionName: 'Чэмпіянат',
                date: '2026-05-10',
            },
        ])

        const wrapper = mountPage()
        await flushPromises()

        expect(getCupEvents).toHaveBeenCalledWith('42', {
            page: 1,
            perPage: 50,
        })
        expect(getEventsByIds).toHaveBeenCalledWith(['9'])
        expect(wrapper.text()).toContain('Кубак')
        expect(wrapper.get('[data-testid="columns"]').text()).not.toContain(
            'actions',
        )
        expect(wrapper.find('.action-menu').exists()).toBe(false)
        expect(wrapper.find('.pi-check-square').attributes('aria-label')).toBe(
            'Бачныя',
        )
    })

    it('renders authenticated card and stage controls', async () => {
        auth.isAuthenticated = true
        getCup.mockResolvedValue({
            id: '42',
            name: 'Кубак',
            year: 2026,
            type: 'master',
            eventsCount: '1',
            groups: [{ id: 'M21', name: 'М21' }],
            visible: true,
        })
        getCupEvents.mockResolvedValue({
            data: [
                {
                    id: '7',
                    cupId: '42',
                    eventId: '9',
                    points: '10',
                    created: {},
                    updated: {},
                },
            ],
            headers: {},
        })
        getEventsByIds.mockResolvedValue([])
        getUsers.mockResolvedValue([])

        const wrapper = mountPage()
        await flushPromises()

        expect(wrapper.find('.action-menu').exists()).toBe(true)
        expect(wrapper.get('[data-testid="columns"]').text()).toContain(
            'actions',
        )
        expect(wrapper.html()).toContain('/cups/42/event/create')
        expect(wrapper.html()).toContain('/cups/42/7/edit')
    })
})
