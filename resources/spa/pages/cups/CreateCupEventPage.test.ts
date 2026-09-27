// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
import { beforeEach, describe, expect, it, vi } from 'vitest'
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
})
