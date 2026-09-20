// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { afterEach, describe, expect, it, vi } from 'vitest'
import CupEventViewPage from './CupEventViewPage.vue'

const {
    getCup,
    getCupEvent,
    getCupEventPoints,
    getEventsByIds,
    getClubOptions,
    route,
} = vi.hoisted(() => ({
    getCup: vi.fn(),
    getCupEvent: vi.fn(),
    getCupEventPoints: vi.fn(),
    getEventsByIds: vi.fn(),
    getClubOptions: vi.fn(),
    route: { params: { cupEventId: '7' } },
}))

vi.mock('../../api/cups', () => ({ getCup, getCupEvent, getCupEventPoints }))
vi.mock('../../api/events', () => ({ getEventsByIds }))
vi.mock('../../api/clubs', () => ({ getClubOptions }))
vi.mock('vue-router', () => ({
    RouterLink: { props: ['to'], template: '<a :href="to"><slot /></a>' },
    useRoute: () => route,
    useRouter: () => ({ replace: vi.fn() }),
}))

function mountPage() {
    return mount(CupEventViewPage, {
        global: {
            stubs: {
                Card: {
                    template:
                        '<section><slot name="title" /><slot name="content" /></section>',
                },
                Message: true,
                Select: true,
                InputText: true,
                FilterPanel: { template: '<div><slot /></div>' },
                ListingTable: {
                    props: ['items'],
                    template: '<div>{{ items.length }}</div>',
                },
            },
        },
    })
}

describe('cup event view page', () => {
    afterEach(() => vi.resetAllMocks())

    it('loads the cup-event card and first group standings page', async () => {
        getCupEvent.mockResolvedValue({
            id: '7',
            cupId: '4',
            eventId: '9',
            points: '100',
        })
        getCup.mockResolvedValue({
            id: '4',
            name: 'Кубак',
            type: 'bike',
            groups: [{ id: 'M21', name: 'М21' }],
        })
        getEventsByIds.mockResolvedValue([
            {
                id: '9',
                competitionId: '3',
                competitionName: 'Старт',
                name: 'Этап',
                date: '2026-09-21',
            },
        ])
        getClubOptions.mockResolvedValue([])
        getCupEventPoints.mockResolvedValue({ data: [], headers: {} })

        const wrapper = mountPage()
        await flushPromises()

        expect(getCupEventPoints).toHaveBeenCalledWith('7', {
            groupId: 'M21',
            name: '',
            page: 1,
            perPage: 50,
        })
        expect(wrapper.text()).toContain('Кубак')
        expect(wrapper.html()).toContain('/app/events/9')
        expect(wrapper.html()).toContain('/app/competitions/3')
    })
})
