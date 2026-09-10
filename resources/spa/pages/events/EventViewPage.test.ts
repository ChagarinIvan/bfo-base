// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
import { describe, expect, it, vi } from 'vitest'
import EventViewPage from './EventViewPage.vue'

const { getEvent, getEventDistances, getPersonProtocolLines } = vi.hoisted(
    () => ({
        getEvent: vi.fn(),
        getEventDistances: vi.fn(),
        getPersonProtocolLines: vi.fn(),
    }),
)

vi.mock('../../api/events', () => ({ getEvent }))
vi.mock('../../api/distances', () => ({ getEventDistances }))
vi.mock('../../api/protocolLines', () => ({ getPersonProtocolLines }))
vi.mock('../../api/users', () => ({ getUsers: vi.fn().mockResolvedValue([]) }))
vi.mock('../../stores/auth', () => ({
    useAuthStore: () => ({ isAuthenticated: false }),
}))
vi.mock('vue-router', () => ({
    RouterLink: { props: ['to'], template: '<a :href="to"><slot /></a>' },
    useRoute: () => ({ params: { eventId: '42' } }),
    useRouter: () => ({ replace: vi.fn() }),
}))

describe('event view page', () => {
    it('loads the selected distance and renders public event result links', async () => {
        getEvent.mockResolvedValue({
            id: '42',
            competitionId: '9',
            name: 'Этап',
            description: 'Апісанне',
            date: '2026-05-10',
            participantsCount: 1,
            cups: [{ id: '3', name: 'Кубак', year: 2026 }],
        })
        getEventDistances.mockResolvedValue([
            {
                id: '7',
                eventId: '42',
                groupName: 'M21',
                length: 10,
                points: 100,
                disqual: false,
            },
        ])
        getPersonProtocolLines.mockResolvedValue({
            data: [
                {
                    id: '11',
                    personId: '5',
                    serialNumber: '1',
                    firstname: 'Ян',
                    lastname: 'Іваноў',
                    distanceId: '7',
                    eventId: null,
                    competitionId: null,
                    competitionName: null,
                    eventName: null,
                    eventDate: null,
                    groupName: null,
                    year: '2000',
                    time: '00:42:00',
                    place: '1',
                    completeRank: null,
                    club: 'Клуб',
                    clubId: '8',
                    clubName: 'Клуб',
                    rank: 'I',
                    points: 100,
                    vk: false,
                    activateRank: null,
                },
            ],
            headers: {
                'x-pagination-current-page': '1',
                'x-pagination-per-page': '20',
                'x-pagination-total': '1',
                'x-pagination-last-page': '1',
            },
        })

        const wrapper = mount(EventViewPage, {
            global: { plugins: [PrimeVue] },
        })
        await flushPromises()

        expect(getPersonProtocolLines).toHaveBeenCalledWith({
            distanceId: '7',
            name: undefined,
            withClub: 1,
            page: 1,
            perPage: 20,
        })
        expect(wrapper.text()).toContain('Кубак 2026')
        expect(wrapper.find('a[href="/app/persons/5"]').exists()).toBe(true)
        expect(wrapper.find('a[href="/app/clubs/8"]').exists()).toBe(true)
        expect(wrapper.text()).not.toContain('Рэдагаваць')
    })
})
