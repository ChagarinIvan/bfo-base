// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import EditCupEventPage from './EditCupEventPage.vue'
import CupEventForm from './CupEventForm.vue'

const {
    getCup,
    getCupEvent,
    updateCupEvent,
    getCupEventOptions,
    getEventsByIds,
    push,
} = vi.hoisted(() => ({
    getCup: vi.fn(),
    getCupEvent: vi.fn(),
    updateCupEvent: vi.fn(),
    getCupEventOptions: vi.fn(),
    getEventsByIds: vi.fn(),
    push: vi.fn(),
}))

vi.mock('../../api/cups', () => ({ getCup, getCupEvent, updateCupEvent }))
vi.mock('../../api/events', () => ({ getCupEventOptions, getEventsByIds }))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { cupId: '42', cupEventId: '7' } }),
    useRouter: () => ({ push }),
}))
vi.mock('primevue/usetoast', () => ({ useToast: () => ({ add: vi.fn() }) }))

describe('edit cup event page', () => {
    beforeEach(() => vi.resetAllMocks())

    it('loads a stage, updates it and returns to its cup detail page', async () => {
        getCup.mockResolvedValue({ id: '42', year: 2026 })
        getCupEvent.mockResolvedValue({ id: '7', eventId: '9', points: '100' })
        getCupEventOptions.mockResolvedValue([])
        getEventsByIds.mockResolvedValue([])
        updateCupEvent.mockResolvedValue({ id: '7' })

        const wrapper = mount(EditCupEventPage, {
            global: { plugins: [PrimeVue] },
        })
        await flushPromises()
        wrapper
            .findComponent(CupEventForm)
            .vm.$emit('submit', { eventId: 10, points: 75 })
        await flushPromises()

        expect(updateCupEvent).toHaveBeenCalledWith('7', {
            eventId: 10,
            points: 75,
        })
        expect(push).toHaveBeenCalledWith('/app/cups/42')
    })

    it('shows field validation errors from the update request', async () => {
        getCup.mockResolvedValue({ id: '42', year: 2026 })
        getCupEvent.mockResolvedValue({ id: '7', eventId: '9', points: '100' })
        getCupEventOptions.mockResolvedValue([])
        getEventsByIds.mockResolvedValue([])
        updateCupEvent.mockRejectedValue({
            isAxiosError: true,
            response: {
                status: 422,
                data: { errors: [{ field: 'points', message: 'Required' }] },
            },
        })

        const wrapper = mount(EditCupEventPage, {
            global: { plugins: [PrimeVue] },
        })
        await flushPromises()
        wrapper
            .findComponent(CupEventForm)
            .vm.$emit('submit', { eventId: 9, points: 0 })
        await flushPromises()

        expect(wrapper.findComponent(CupEventForm).props('errors')).toEqual({
            points: 'Required',
        })
    })

    it('shows the stage not-found state when the stage is unavailable', async () => {
        getCup.mockResolvedValue({ id: '42', year: 2026 })
        getCupEvent.mockRejectedValue({
            isAxiosError: true,
            response: { status: 404 },
        })

        const wrapper = mount(EditCupEventPage, {
            global: { plugins: [PrimeVue] },
        })
        await flushPromises()

        expect(wrapper.text()).toContain('Этап кубка не знойдзены.')
    })
})
