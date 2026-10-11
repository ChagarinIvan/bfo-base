// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
import Select from 'primevue/select'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import CreateCupEventPage from './CreateCupEventPage.vue'
import CupEventForm from './CupEventForm.vue'

const { createCupEvent, getCup, getCupEventOptions, push } = vi.hoisted(() => ({
    createCupEvent: vi.fn(),
    getCup: vi.fn(),
    getCupEventOptions: vi.fn(),
    push: vi.fn(),
}))

vi.mock('../../api/cups', () => ({ createCupEvent, getCup }))
vi.mock('../../api/events', () => ({ getCupEventOptions }))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { cupId: '42' } }),
    useRouter: () => ({ push }),
}))
vi.mock('primevue/usetoast', () => ({ useToast: () => ({ add: vi.fn() }) }))

describe('create cup event page', () => {
    beforeEach(() => vi.resetAllMocks())
    afterEach(() => vi.useRealTimers())

    it('creates a stage for the route cup and returns to its detail page', async () => {
        getCup.mockResolvedValue({ id: '42', year: 2026 })
        getCupEventOptions.mockResolvedValue([])
        createCupEvent.mockResolvedValue({ id: '7' })

        const wrapper = mount(CreateCupEventPage, {
            global: { plugins: [PrimeVue] },
        })
        await flushPromises()
        wrapper
            .findComponent(CupEventForm)
            .vm.$emit('submit', { eventId: 9, points: 100 })
        await flushPromises()

        expect(createCupEvent).toHaveBeenCalledWith({
            cupId: 42,
            eventId: 9,
            points: 100,
        })
        expect(push).toHaveBeenCalledWith('/app/cups/42')
    })

    it('shows field validation errors from the create request', async () => {
        getCup.mockResolvedValue({ id: '42', year: 2026 })
        getCupEventOptions.mockResolvedValue([])
        createCupEvent.mockRejectedValue({
            isAxiosError: true,
            response: {
                status: 422,
                data: { errors: [{ field: 'eventId', message: 'Required' }] },
            },
        })

        const wrapper = mount(CreateCupEventPage, {
            global: { plugins: [PrimeVue] },
        })
        await flushPromises()
        wrapper
            .findComponent(CupEventForm)
            .vm.$emit('submit', { eventId: 0, points: 100 })
        await flushPromises()

        expect(wrapper.findComponent(CupEventForm).props('errors')).toEqual({
            eventId: 'Required',
        })
    })

    it('shows the cup not-found state when the route cup is unavailable', async () => {
        getCup.mockRejectedValue({
            isAxiosError: true,
            response: { status: 404 },
        })

        const wrapper = mount(CreateCupEventPage, {
            global: { plugins: [PrimeVue] },
        })
        await flushPromises()

        expect(wrapper.text()).toContain('Кубак не знойдзены.')
    })

    it('loads the first 20 sorted options and searches beyond them', async () => {
        vi.useFakeTimers()
        getCup.mockResolvedValue({ id: '42', year: 2026 })
        getCupEventOptions
            .mockResolvedValueOnce([{ id: '9', name: 'Sprint' }])
            .mockResolvedValueOnce([{ id: '30', name: 'Forest Sprint' }])
            .mockResolvedValueOnce([{ id: '9', name: 'Sprint' }])

        const wrapper = mount(CreateCupEventPage, {
            global: { plugins: [PrimeVue] },
        })
        await flushPromises()

        expect(getCupEventOptions).toHaveBeenCalledWith({
            year: 2026,
            notRelatedToCup: '42',
            sortBy: 'date',
            sort: 'desc',
            page: 1,
            perPage: 20,
        })
        const form = wrapper.findComponent(CupEventForm)
        expect(form.props('remoteSearch')).toBe(true)
        expect(form.props('events')).toHaveLength(1)

        form.findComponent(Select).vm.$emit('filter', { value: 'Forest' })
        await vi.advanceTimersByTimeAsync(300)
        await flushPromises()
        expect(getCupEventOptions).toHaveBeenLastCalledWith(
            expect.objectContaining({
                competitionOrEventName: 'Forest',
                page: 1,
            }),
        )
        expect(form.props('events')).toEqual([
            expect.objectContaining({ id: '30' }),
        ])

        form.findComponent(Select).vm.$emit('filter', { value: '' })
        await vi.advanceTimersByTimeAsync(300)
        await flushPromises()
        expect(getCupEventOptions).toHaveBeenLastCalledWith({
            year: 2026,
            notRelatedToCup: '42',
            sortBy: 'date',
            sort: 'desc',
            page: 1,
            perPage: 20,
        })
    })

    it('does not search on short input and ignores an older response', async () => {
        vi.useFakeTimers()
        getCup.mockResolvedValue({ id: '42', year: 2026 })
        getCupEventOptions.mockResolvedValueOnce([])
        const pending: Array<(value: unknown) => void> = []
        getCupEventOptions.mockImplementation(
            () => new Promise((resolve) => pending.push(resolve)),
        )

        const wrapper = mount(CreateCupEventPage, {
            global: { plugins: [PrimeVue] },
        })
        await flushPromises()
        const form = wrapper.findComponent(CupEventForm)

        form.findComponent(Select).vm.$emit('filter', { value: 'Fo' })
        await vi.advanceTimersByTimeAsync(300)
        expect(getCupEventOptions).toHaveBeenCalledTimes(1)

        form.findComponent(Select).vm.$emit('filter', { value: 'Forest' })
        await vi.advanceTimersByTimeAsync(300)
        form.findComponent(Select).vm.$emit('filter', { value: 'City' })
        await vi.advanceTimersByTimeAsync(300)

        pending[1]?.([{ id: '8', name: 'City Race' }])
        await flushPromises()
        pending[0]?.([{ id: '9', name: 'Forest Race' }])
        await flushPromises()

        expect(form.props('events')).toEqual([
            expect.objectContaining({ id: '8' }),
        ])
    })
})
