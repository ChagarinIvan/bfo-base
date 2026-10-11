// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
import Select from 'primevue/select'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import CreateCupEventPage from './CreateCupEventPage.vue'
import CupEventForm from './CupEventForm.vue'

const { createCupEvent, getCup, getCupEventOptionsPage, push } = vi.hoisted(
    () => ({
        createCupEvent: vi.fn(),
        getCup: vi.fn(),
        getCupEventOptionsPage: vi.fn(),
        push: vi.fn(),
    }),
)

vi.mock('../../api/cups', () => ({ createCupEvent, getCup }))
vi.mock('../../api/events', () => ({ getCupEventOptionsPage }))
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
        getCupEventOptionsPage.mockResolvedValue({ data: [], headers: {} })
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
        getCupEventOptionsPage.mockResolvedValue({ data: [], headers: {} })
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

    it('loads sorted pages of 20 and searches across the server result', async () => {
        vi.useFakeTimers()
        getCup.mockResolvedValue({ id: '42', year: 2026 })
        getCupEventOptionsPage.mockResolvedValue({
            data: [{ id: '9', name: 'Sprint', competitionName: 'Forest Cup' }],
            headers: {
                'x-pagination-current-page': '1',
                'x-pagination-per-page': '20',
                'x-pagination-has-next': 'true',
            },
        })

        const wrapper = mount(CreateCupEventPage, {
            global: { plugins: [PrimeVue] },
        })
        await flushPromises()

        expect(getCupEventOptionsPage).toHaveBeenCalledWith({
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

        form.vm.$emit('page', { page: 1, rows: 20 })
        await flushPromises()
        expect(getCupEventOptionsPage).toHaveBeenLastCalledWith(
            expect.objectContaining({ page: 2, perPage: 20 }),
        )

        form.findComponent(Select).vm.$emit('filter', { value: 'Forest' })
        await vi.advanceTimersByTimeAsync(300)
        await flushPromises()
        expect(getCupEventOptionsPage).toHaveBeenLastCalledWith(
            expect.objectContaining({
                competitionOrEventName: 'Forest',
                page: 1,
            }),
        )

        form.findComponent(Select).vm.$emit('filter', { value: '' })
        await vi.advanceTimersByTimeAsync(300)
        await flushPromises()
        expect(getCupEventOptionsPage).toHaveBeenLastCalledWith({
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
        getCupEventOptionsPage.mockResolvedValueOnce({ data: [], headers: {} })
        const pending: Array<(value: unknown) => void> = []
        getCupEventOptionsPage.mockImplementation(
            () => new Promise((resolve) => pending.push(resolve)),
        )

        const wrapper = mount(CreateCupEventPage, {
            global: { plugins: [PrimeVue] },
        })
        await flushPromises()
        const form = wrapper.findComponent(CupEventForm)

        form.findComponent(Select).vm.$emit('filter', { value: 'Fo' })
        await vi.advanceTimersByTimeAsync(300)
        expect(getCupEventOptionsPage).toHaveBeenCalledTimes(1)

        form.findComponent(Select).vm.$emit('filter', { value: 'Forest' })
        await vi.advanceTimersByTimeAsync(300)
        form.findComponent(Select).vm.$emit('filter', { value: 'City' })
        await vi.advanceTimersByTimeAsync(300)

        pending[1]?.({
            data: [{ id: '8', name: 'City Race' }],
            headers: {},
        })
        await flushPromises()
        pending[0]?.({
            data: [{ id: '9', name: 'Forest Race' }],
            headers: {},
        })
        await flushPromises()

        expect(form.props('events')).toEqual([
            expect.objectContaining({ id: '8' }),
        ])
    })
})
