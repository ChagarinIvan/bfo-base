// @vitest-environment happy-dom

import { flushPromises, mount, type VueWrapper } from '@vue/test-utils'
import { reactive, ref } from 'vue'
import PrimeVue from 'primevue/config'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import EventViewPage from './events/EventViewPage.vue'
import PersonViewPage from './persons/PersonViewPage.vue'
import CupEventViewPage from './cups/CupEventViewPage.vue'
import { personContextKey } from './persons/personContext'
import ListingTable from '../components/ListingTable.vue'
import SlicePaginator from '../components/SlicePaginator.vue'
import type { Impression } from '../api/types'

const { state, getUsers, refreshUsers, getLines, getPoints } = vi.hoisted(
    () => ({
        state: { auth: { isAuthenticated: true } },
        getUsers: vi.fn(),
        refreshUsers: vi.fn(),
        getLines: vi.fn(),
        getPoints: vi.fn(),
    }),
)
vi.mock('../stores/auth', () => ({ useAuthStore: () => state.auth }))
vi.mock('../api/users', () => ({ getUsers, refreshUsers }))
vi.mock('../api/protocolLines', () => ({ getPersonProtocolLines: getLines }))
vi.mock('../api/years', () => ({ getYears: async () => [2026] }))
vi.mock('../api/clubs', () => ({ getClubOptions: async () => [] }))
vi.mock('../api/competitions', () => ({
    getCompetition: async () => ({ id: '3', name: 'Cup' }),
}))
vi.mock('../api/events', () => ({
    getEvent: async () => ({
        id: '9',
        competitionId: '3',
        name: 'Event',
        date: '2026-01-01',
    }),
    getEventsByIds: async () => [
        { id: '9', competitionId: '3', name: 'Event' },
    ],
}))
vi.mock('../api/distances', () => ({
    getEventDistances: async () => [{ id: '12', groupName: 'M21' }],
}))
vi.mock('../api/cups', () => ({
    getCup: async () => ({
        id: '4',
        name: 'Cup',
        type: 'sprint',
        groups: [{ id: 'M21', name: 'M21' }],
    }),
    getCupEvent: async () => ({
        id: '7',
        cupId: '4',
        eventId: '9',
        points: '100',
    }),
    getCupEventPoints: getPoints,
    getCupEventContexts: async () => [],
}))
vi.mock('vue-router', () => ({
    RouterLink: { props: ['to'], template: '<a :href="to"><slot /></a>' },
    useRoute: () => ({
        params: { eventId: '9', personId: '5', cupEventId: '7' },
        query: {},
        hash: '',
    }),
    useRouter: () => ({ push: vi.fn(), replace: vi.fn() }),
}))

state.auth = reactive(state.auth)
const created: Impression = { at: '2026-10-27T10:00:00+00:00', by: '7' }
const updated: Impression = { at: '2026-11-01T12:30:00+00:00', by: '8' }
const line = {
    id: '11',
    personId: '5',
    firstname: 'Ivan',
    lastname: 'Test',
    distanceId: '12',
    eventId: '9',
    competitionId: '3',
    competitionName: 'Cup',
    eventName: 'Event',
    groupName: 'M21',
    eventDate: '2026-01-01',
    personName: 'Test Ivan',
    personYear: 2000,
    points: '100',
    created,
    updated,
}
const pages = [
    {
        component: EventViewPage,
        tableId: 'event-protocol-lines',
        oldColumn: 'lastname',
    },
    {
        component: PersonViewPage,
        tableId: 'person-participation',
        oldColumn: 'name',
    },
    {
        component: CupEventViewPage,
        tableId: 'cup-event-points',
        oldColumn: 'person',
    },
]
const wrappers: VueWrapper[] = []
function openPage(component: (typeof pages)[number]['component']) {
    const wrapper = mount(component, {
        global: {
            plugins: [PrimeVue],
            provide: {
                [personContextKey as symbol]: ref({
                    id: '5',
                    firstname: 'Ivan',
                    lastname: 'Test',
                }),
            },
            stubs: {
                Select: true,
                InputText: true,
                DateFilter: true,
                YearFilter: true,
                RouterLink: {
                    props: ['to'],
                    template: '<a :href="to"><slot /></a>',
                },
            },
        },
    })
    wrappers.push(wrapper)
    return wrapper
}
function chooser(wrapper: VueWrapper, label: string) {
    return wrapper
        .findAll('.listing-table__column-options label')
        .find((item) => item.text() === label)
}
function auditDates(wrapper: VueWrapper, at: string) {
    return wrapper.findAll(`.listing-table time[datetime="${at}"]`)
}

beforeEach(() => {
    vi.clearAllMocks()
    localStorage.clear()
    state.auth.isAuthenticated = true
    const users = [
        { id: '7', name: 'Creator', email: 'creator@example.test' },
        { id: '8', name: 'Editor', email: 'editor@example.test' },
    ]
    getUsers.mockResolvedValue(users)
    refreshUsers.mockResolvedValue(users)
    getLines.mockResolvedValue({ data: [line], headers: {} })
    getPoints.mockResolvedValue({ data: [line], headers: {} })
})
afterEach(() => {
    wrappers.forEach((wrapper) => {
        if (wrapper.exists()) {
            wrapper.unmount()
        }
    })
    wrappers.length = 0
})

describe.each(pages)(
    '$tableId protocol line impressions',
    ({ component, tableId, oldColumn }) => {
        it('defaults hidden, toggles independently, retains choice through pagination and remount, resets with cleared preferences', async () => {
            const wrapper = openPage(component)
            await flushPromises()
            expect(auditDates(wrapper, created.at)).toHaveLength(0)
            expect(auditDates(wrapper, updated.at)).toHaveLength(0)
            expect(chooser(wrapper, 'Створана')).toBeDefined()
            expect(chooser(wrapper, 'Зменена')).toBeDefined()
            await chooser(wrapper, 'Створана')!.get('input').setValue(true)
            expect(auditDates(wrapper, created.at)).toHaveLength(1)
            expect(wrapper.get('.listing-table .impression-user').text()).toBe(
                'creator@example.test',
            )
            expect(auditDates(wrapper, updated.at)).toHaveLength(0)
            await chooser(wrapper, 'Зменена')!.get('input').setValue(true)
            await chooser(wrapper, 'Створана')!.get('input').setValue(false)
            expect(auditDates(wrapper, created.at)).toHaveLength(0)
            expect(auditDates(wrapper, updated.at)).toHaveLength(1)
            wrapper
                .getComponent(SlicePaginator)
                .vm.$emit('page', { page: 1, rows: 20 })
            await flushPromises()
            expect(auditDates(wrapper, updated.at)).toHaveLength(1)
            expect(
                JSON.parse(
                    localStorage.getItem(`bfo.table.${tableId}.authenticated`)!,
                ),
            ).toContain('updated')
            wrapper.unmount()
            const reopened = openPage(component)
            await flushPromises()
            expect(auditDates(reopened, updated.at)).toHaveLength(1)
            expect(auditDates(reopened, created.at)).toHaveLength(0)
            reopened.unmount()
            localStorage.removeItem(`bfo.table.${tableId}.authenticated`)
            const reset = openPage(component)
            await flushPromises()
            expect(auditDates(reset, updated.at)).toHaveLength(0)
        })

        it('preserves old preferences without enabling the new columns', async () => {
            localStorage.setItem(
                `bfo.table.${tableId}.authenticated`,
                JSON.stringify([oldColumn]),
            )
            const wrapper = openPage(component)
            await flushPromises()
            const checked = wrapper
                .findAll('.listing-table__column-options input')
                .filter((input) => (input.element as HTMLInputElement).checked)
            expect(checked).toHaveLength(1)
            expect(auditDates(wrapper, created.at)).toHaveLength(0)
            expect(auditDates(wrapper, updated.at)).toHaveLength(0)
        })

        it('hides audit from guests even with stale fields and stored private keys, without requesting users', async () => {
            state.auth.isAuthenticated = false
            localStorage.setItem(
                `bfo.table.${tableId}.guest`,
                JSON.stringify([oldColumn, 'created', 'updated']),
            )
            const wrapper = openPage(component)
            await flushPromises()
            expect(chooser(wrapper, 'Створана')).toBeUndefined()
            expect(chooser(wrapper, 'Зменена')).toBeUndefined()
            expect(auditDates(wrapper, created.at)).toHaveLength(0)
            expect(auditDates(wrapper, updated.at)).toHaveLength(0)
            expect(getUsers).not.toHaveBeenCalled()
            expect(refreshUsers).not.toHaveBeenCalled()
        })

        it('removes audit on logout even while the old authenticated response remains in memory', async () => {
            const wrapper = openPage(component)
            await flushPromises()
            await chooser(wrapper, 'Створана')!.get('input').setValue(true)
            expect(auditDates(wrapper, created.at)).toHaveLength(1)
            state.auth.isAuthenticated = false
            await flushPromises()
            expect(chooser(wrapper, 'Створана')).toBeUndefined()
            expect(auditDates(wrapper, created.at)).toHaveLength(0)
        })

        it('keeps results available when author lookup fails', async () => {
            getUsers.mockRejectedValue(new Error('users unavailable'))
            refreshUsers.mockRejectedValue(new Error('users unavailable'))
            const wrapper = openPage(component)
            await flushPromises()
            expect(wrapper.findComponent(ListingTable).exists()).toBe(true)
            await chooser(wrapper, 'Створана')!.get('input').setValue(true)
            await flushPromises()
            expect(auditDates(wrapper, created.at)).toHaveLength(1)
            expect(
                wrapper.find('.listing-table .impression-user').text(),
            ).toContain('7')
        })

        it('loads authors as a list for 50 rows rather than once per row', async () => {
            const rows = Array.from({ length: 50 }, (_, index) => ({
                ...line,
                id: String(index + 1),
            }))
            getLines.mockResolvedValue({ data: rows, headers: {} })
            getPoints.mockResolvedValue({ data: rows, headers: {} })
            const wrapper = openPage(component)
            await flushPromises()
            await chooser(wrapper, 'Створана')!.get('input').setValue(true)
            await chooser(wrapper, 'Зменена')!.get('input').setValue(true)
            await flushPromises()
            expect(auditDates(wrapper, created.at)).toHaveLength(50)
            expect(auditDates(wrapper, updated.at)).toHaveLength(50)
            expect(getUsers).toHaveBeenCalledOnce()
            expect(refreshUsers).not.toHaveBeenCalled()
        }, 20000)

        it('supports empty results and missing impressions without substituting another entity audit', async () => {
            getLines.mockResolvedValue({ data: [], headers: {} })
            getPoints.mockResolvedValue({ data: [], headers: {} })
            const wrapper = openPage(component)
            await flushPromises()
            await chooser(wrapper, 'Створана')!.get('input').setValue(true)
            expect(auditDates(wrapper, created.at)).toHaveLength(0)
            wrapper.unmount()
            getLines.mockResolvedValue({
                data: [{ ...line, created: undefined }],
                headers: {},
            })
            getPoints.mockResolvedValue({
                data: [{ ...line, created: undefined }],
                headers: {},
            })
            const missing = openPage(component)
            await flushPromises()
            expect(
                missing.find('.listing-table .impression-empty').exists(),
            ).toBe(true)
        })
    },
)

it('carries the old cup standings preferences into the authenticated namespace without changing other tables', async () => {
    localStorage.setItem('bfo.table.cup-event-points.guest', '["time"]')
    const wrapper = openPage(CupEventViewPage)
    await flushPromises()
    expect(
        localStorage.getItem('bfo.table.cup-event-points.authenticated'),
    ).toBe('["time"]')
    expect(
        wrapper
            .findAll('.listing-table__column-options input')
            .filter((input) => (input.element as HTMLInputElement).checked),
    ).toHaveLength(1)
    expect(localStorage.getItem('bfo.table.cup-event-points.guest')).toBe(
        '["time"]',
    )
    expect(
        localStorage.getItem('bfo.table.person-participation.authenticated'),
    ).toBeNull()
    expect(
        localStorage.getItem('bfo.table.event-protocol-lines.authenticated'),
    ).toBeNull()
})

it('does not replace existing authenticated cup preferences with an older guest selection', async () => {
    localStorage.setItem('bfo.table.cup-event-points.guest', '["time"]')
    localStorage.setItem(
        'bfo.table.cup-event-points.authenticated',
        '["person","updated"]',
    )
    const wrapper = openPage(CupEventViewPage)
    await flushPromises()
    expect(
        localStorage.getItem('bfo.table.cup-event-points.authenticated'),
    ).toBe('["person","updated"]')
    expect(auditDates(wrapper, updated.at)).toHaveLength(1)
})

it('carries only public keys from legacy cup preferences', async () => {
    localStorage.setItem(
        'bfo.table.cup-event-points.guest',
        '["time","created","unknown"]',
    )
    const wrapper = openPage(CupEventViewPage)
    await flushPromises()
    expect(
        localStorage.getItem('bfo.table.cup-event-points.authenticated'),
    ).toBe('["time"]')
    expect(auditDates(wrapper, created.at)).toHaveLength(0)
})
