// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
import Select from 'primevue/select'
import { afterEach, describe, expect, it, vi } from 'vitest'
import ActionButton from '../../components/actions/ActionButton.vue'
import EventViewPage from './EventViewPage.vue'

const {
    auth,
    getCompetition,
    getEvent,
    getEventDistances,
    getPersonProtocolLines,
    route,
} = vi.hoisted(
    () => ({
        auth: { isAuthenticated: false },
        getCompetition: vi.fn(),
        getEvent: vi.fn(),
        getEventDistances: vi.fn(),
        getPersonProtocolLines: vi.fn(),
        route: { params: { eventId: '42' }, query: {}, hash: '' },
    }),
)

vi.mock('../../api/competitions', () => ({ getCompetition }))
vi.mock('../../api/events', () => ({ getEvent }))
vi.mock('../../api/distances', () => ({ getEventDistances }))
vi.mock('../../api/protocolLines', () => ({ getPersonProtocolLines }))
vi.mock('../../api/users', () => ({ getUsers: vi.fn().mockResolvedValue([]) }))
vi.mock('../../stores/auth', () => ({
    useAuthStore: () => auth,
}))
vi.mock('vue-router', () => ({
    RouterLink: { props: ['to'], template: '<a :href="to"><slot /></a>' },
    useRoute: () => route,
    useRouter: () => ({ replace: vi.fn() }),
}))

describe('event view page', () => {
    afterEach(() => {
        auth.isAuthenticated = false
        route.query = {}
        route.hash = ''
        vi.useRealTimers()
    })

    it('loads the selected distance and renders public event result links', async () => {
        getEvent.mockResolvedValue({
            id: '42',
            competitionId: '9',
            name: 'Этап',
            description: 'Апісанне',
            date: '2026-05-10',
            participantsCount: 1,
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
        getCompetition.mockResolvedValue({
            id: '9',
            name: 'Кубак Беларусі',
            description: '',
            from: '2026-05-10',
            to: '2026-05-10',
            year: 2026,
            mass: false,
        })
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
                'x-pagination-per-page': '100',
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
            perPage: 100,
        })
        expect(wrapper.find('a[href="/app/persons/5"]').exists()).toBe(true)
        expect(wrapper.find('a[href="/app/clubs/8"]').exists()).toBe(true)
        expect(wrapper.find('a[href="/app/competitions/9"]').text()).toBe(
            'Кубак Беларусі',
        )
        expect(wrapper.find('.competition-details-card').exists()).toBe(true)
        expect(wrapper.find('.filter-card').exists()).toBe(true)
        expect(wrapper.find('.events-table').exists()).toBe(true)
        expect(wrapper.findComponent(Select).props()).toMatchObject({
            filter: true,
            filterMatchMode: 'contains',
        })
        expect(wrapper.text()).not.toContain('Кубкі')
        expect(wrapper.text()).not.toContain('Рэдагаваць')
        expect(wrapper.text()).not.toContain('Актывацыя разраду')
    })

    it('uses the standard styled action for assigning a participant', async () => {
        auth.isAuthenticated = true

        const wrapper = mount(EventViewPage, {
            global: { plugins: [PrimeVue] },
        })
        await flushPromises()

        const assignAction = wrapper
            .findAllComponents(ActionButton)
            .find((action) => action.props('label') === 'Прызначыць удзельніка')

        expect(assignAction?.props()).toMatchObject({
            icon: 'pi pi-user-plus',
            severity: 'success',
        })
        expect(wrapper.text()).toContain('Актывацыя разраду')
    })

    it('filters protocol lines after three characters without requiring Enter', async () => {
        vi.useFakeTimers()

        const wrapper = mount(EventViewPage, {
            global: { plugins: [PrimeVue] },
        })
        await flushPromises()
        getPersonProtocolLines.mockClear()

        await wrapper.find('#event-name-filter').setValue('Ів')

        expect(wrapper.text()).toContain('Увядзіце не менш за 3 сімвалы.')
        expect(getPersonProtocolLines).toHaveBeenCalledWith({
            distanceId: '7',
            name: undefined,
            withClub: 1,
            page: 1,
            perPage: 100,
        })

        getPersonProtocolLines.mockClear()

        await wrapper.find('#event-name-filter').setValue('Іва')
        await vi.advanceTimersByTimeAsync(299)
        expect(getPersonProtocolLines).not.toHaveBeenCalled()

        await vi.advanceTimersByTimeAsync(1)
        await flushPromises()

        expect(getPersonProtocolLines).toHaveBeenCalledWith({
            distanceId: '7',
            name: 'Іва',
            withClub: 1,
            page: 1,
            perPage: 100,
        })
    })

    it('selects the linked distance and scrolls to the protocol line anchor', async () => {
        const scrollIntoView = vi.fn()
        Object.defineProperty(Element.prototype, 'scrollIntoView', {
            configurable: true,
            value: scrollIntoView,
        })
        route.query = { distanceId: '7' }
        route.hash = '#11'

        const wrapper = mount(EventViewPage, {
            attachTo: document.body,
            global: { plugins: [PrimeVue] },
        })
        await flushPromises()

        expect(getPersonProtocolLines).toHaveBeenLastCalledWith({
            distanceId: '7',
            name: undefined,
            withClub: 1,
            page: 1,
            perPage: 100,
        })
        expect(wrapper.find('#11').exists()).toBe(true)
        expect(document.getElementById('11')).not.toBeNull()
        expect(document.getElementById('11')?.scrollIntoView).toBe(
            scrollIntoView,
        )
        expect(scrollIntoView).toHaveBeenCalledWith({
            behavior: 'smooth',
            block: 'center',
        })
        wrapper.unmount()
    })
})
