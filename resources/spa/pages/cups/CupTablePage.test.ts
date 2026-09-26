// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'
import CupTablePage from './CupTablePage.vue'
import ListingTable from '../../components/ListingTable.vue'
import { cupContextKey } from './cupContext'

const { getCup, getCupEvents, getCupTable, getEventsByIds, route } = vi.hoisted(
    () => ({
        getCup: vi.fn(),
        getCupEvents: vi.fn(),
        getCupTable: vi.fn(),
        getEventsByIds: vi.fn(),
        route: { params: { cupId: '42', groupId: 'M_0_' } },
    }),
)

vi.mock('../../api/cups', () => ({ getCup, getCupEvents, getCupTable }))
vi.mock('../../api/events', () => ({ getEventsByIds }))
vi.mock('../../stores/auth', () => ({
    useAuthStore: () => ({ isAuthenticated: false }),
}))
vi.mock('vue-router', () => ({
    useRoute: () => route,
    useRouter: () => ({ replace: vi.fn() }),
}))

describe('cup table page', () => {
    it('loads the selected group and renders stage points', async () => {
        getCup.mockResolvedValue({
            id: '42',
            name: 'Кубак',
            year: 2026,
            type: 'master',
            eventsCount: '1',
            groups: [{ id: 'M_0_', name: 'М0' }],
        })
        getCupEvents.mockResolvedValue({
            data: [{ id: '7', eventId: '9' }],
        })
        getEventsByIds.mockResolvedValue([
            { id: '9', date: '2026-05-10', name: 'Этап' },
        ])
        getCupTable.mockResolvedValue({
            data: [
                {
                    place: 1,
                    personId: '10',
                    personName: 'Иван Иванов',
                    personYear: 1990,
                    clubName: 'Клуб',
                    stages: {
                        '7': {
                            stageId: 7,
                            points: 100,
                            counted: true,
                            distanceId: '8',
                            protocolLineId: '11',
                        },
                    },
                    totalPoints: '100',
                    averagePoints: '100',
                },
            ],
            headers: {},
        })

        const wrapper = mount(CupTablePage, {
            global: {
                provide: {
                    [cupContextKey as symbol]: ref({
                        id: '42',
                        name: 'Кубак',
                        year: 2026,
                        type: 'master',
                        eventsCount: '1',
                        groups: [{ id: 'M_0_', name: 'М0' }],
                        visible: true,
                    }),
                },
                stubs: {
                    Message: true,
                    Select: true,
                    InputText: true,
                    FilterPanel: { template: '<div><slot /></div>' },
                    CupTypeIcon: true,
                    ActionButton: true,
                    ListingTable: {
                        props: ['items', 'columns'],
                        template:
                            '<div>{{ items[0]?.personName }}<slot name="cell-stage-7" :data="items[0]" /></div>',
                    },
                    RouterLink: { template: '<a><slot /></a>' },
                },
            },
        })

        await flushPromises()

        expect(getCupTable).toHaveBeenCalledWith(
            '42',
            'M_0_',
            { page: 1, perPage: 50 },
            expect.any(AbortSignal),
        )
        expect(wrapper.text()).toContain('Иван Иванов')
        expect(
            wrapper
                .findComponent(ListingTable)
                .props('columns')
                .filter((column) =>
                    [
                        'place',
                        'personYear',
                        'clubName',
                        'totalPoints',
                        'averagePoints',
                    ].includes(column.key),
                )
                .every((column) => column.field === column.key),
        ).toBe(true)
    })
})
