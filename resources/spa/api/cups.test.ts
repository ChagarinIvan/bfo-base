import { describe, expect, it, vi } from 'vitest'
import { api } from './client'
import {
    clearCupCache,
    createCupEvent,
    deleteCup,
    deleteCupEvent,
    exportCupTable,
    getCupEvent,
    getCupEventPoints,
    getCupEventContexts,
    getCupEvents,
    getCupTable,
    updateCupEvent,
} from './cups'

vi.mock('./client', () => ({
    api: { get: vi.fn(), post: vi.fn(), put: vi.fn(), delete: vi.fn() },
}))

describe('cups API', () => {
    it('loads a complete cup table for the selected group', async () => {
        vi.mocked(api.get).mockResolvedValue({
            data: { rows: [] },
            headers: {},
        })

        await getCupTable('42', 'M_0_')

        expect(api.get).toHaveBeenCalledWith('/cups/42/tables/M_0_', {
            params: {},
            signal: undefined,
        })
    })

    it('downloads the cup export through the authenticated API client', async () => {
        vi.mocked(api.get).mockResolvedValue({ data: new Blob(['csv']) })

        await exportCupTable('42')

        expect(api.get).toHaveBeenCalledWith('/cups/42/export', {
            responseType: 'blob',
        })
    })

    it('uses V1 API calls for cup maintenance', async () => {
        vi.mocked(api.post).mockResolvedValue({ data: undefined })
        vi.mocked(api.delete).mockResolvedValue({ data: undefined })

        await clearCupCache()
        await deleteCupEvent('7')
        await deleteCup('42')

        expect(api.post).toHaveBeenCalledWith('/cups/cache-clear')
        expect(api.delete).toHaveBeenCalledWith('/cup-events/7')
        expect(api.delete).toHaveBeenCalledWith('/cups/42')
    })

    it('passes compact cup-stage filters to the nested endpoint', async () => {
        vi.mocked(api.get).mockResolvedValue({ data: [], headers: {} })

        await getCupEvents('42', {
            eventIds: ['10', '20'],
            name: 'Stage',
            date: '2026-05-10',
            page: 2,
            perPage: 50,
        })

        expect(api.get).toHaveBeenCalledWith('/cup-events', {
            params: {
                cupId: '42',
                eventIds: ['10', '20'],
                name: 'Stage',
                date: '2026-05-10',
                page: 2,
                perPage: 50,
            },
        })
    })

    it('uses aggregate endpoints for a cup stage', async () => {
        vi.mocked(api.get).mockResolvedValue({ data: { id: '7' } })
        vi.mocked(api.post).mockResolvedValue({ data: { id: '7' } })
        vi.mocked(api.put).mockResolvedValue({ data: { id: '7' } })

        await getCupEvent('7')
        await getCupEventPoints('7', { groupId: 'M21', perPage: 50 })
        await createCupEvent({ cupId: 42, eventId: 9, points: 100 })
        await updateCupEvent('7', { eventId: 9, points: 75 })

        expect(api.get).toHaveBeenCalledWith('/cup-events/7', {
            signal: undefined,
        })
        expect(api.get).toHaveBeenCalledWith('/cup-events/7/points', {
            params: { groupId: 'M21', perPage: 50 },
            signal: undefined,
        })
        expect(api.post).toHaveBeenCalledWith('/cup-events', {
            cupId: 42,
            eventId: 9,
            points: 100,
        })
        expect(api.put).toHaveBeenCalledWith('/cup-events/7', {
            eventId: 9,
            points: 75,
        })
    })

    it('loads compact cup contexts for event table rows', async () => {
        vi.mocked(api.get)
            .mockResolvedValueOnce({
                data: [{ id: '7', cupId: '8', eventId: '10', points: '0' }],
            })
            .mockResolvedValueOnce({
                data: [
                    {
                        id: '8',
                        name: 'Кубак',
                        type: 'sprint',
                        groups: [{ id: 'M_35_', name: 'М35' }],
                    },
                ],
            })

        const contexts = await getCupEventContexts(['10', '20'])

        expect(api.get).toHaveBeenCalledWith('/cup-events', {
            params: { eventIds: ['10', '20'], perPage: 1000 },
        })
        expect(api.get).toHaveBeenLastCalledWith('/cups', {
            params: { ids: ['8'], perPage: 100 },
        })
        expect(contexts).toMatchObject([
            {
                cupName: 'Кубак',
                groups: [{ name: 'М35' }],
                href: '/app/cup-events/7',
            },
        ])
    })
})
