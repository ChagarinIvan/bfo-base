// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import CreateEventPage from './CreateEventPage.vue'
import EventForm from './EventForm.vue'

const { createEvent, push } = vi.hoisted(() => ({
    createEvent: vi.fn(),
    push: vi.fn(),
}))

vi.mock('../../api/events', () => ({ createEvent }))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { competitionId: '42' } }),
    useRouter: () => ({ push }),
}))
vi.mock('primevue/usetoast', () => ({ useToast: () => ({ add: vi.fn() }) }))

describe('create event page', () => {
    beforeEach(() => vi.resetAllMocks())

    it('creates an event with a protocol and opens its processing page', async () => {
        createEvent.mockResolvedValue({ id: '7', processingStatus: 'parsing' })
        const protocol = new File(['protocol'], 'stage.html', {
            type: 'text/html',
        })
        const payload = {
            name: 'Этап',
            description: 'Апісанне',
            date: '2026-05-10',
            protocol,
            url: '',
        }
        const wrapper = mount(CreateEventPage, {
            global: { plugins: [PrimeVue] },
        })

        wrapper.findComponent(EventForm).vm.$emit('submit', payload)
        await flushPromises()

        expect(createEvent).toHaveBeenCalledWith('42', payload)
        expect(push).toHaveBeenCalledWith('/app/events/7')
    })

    it('shows source validation next to the field and retains the form', async () => {
        createEvent.mockRejectedValue({
            isAxiosError: true,
            response: {
                status: 422,
                data: {
                    errors: [
                        { field: 'url', message: 'Няправільная спасылка' },
                    ],
                },
            },
        })
        const wrapper = mount(CreateEventPage, {
            global: { plugins: [PrimeVue] },
        })
        wrapper.findComponent(EventForm).vm.$emit('submit', {
            name: 'Этап',
            description: 'Апісанне',
            date: '2026-05-10',
            url: 'bad-url',
        })
        await flushPromises()

        expect(wrapper.findComponent(EventForm).props('errors')).toEqual({
            url: 'Няправільная спасылка',
        })
        expect(wrapper.findComponent(EventForm).exists()).toBe(true)
        expect(push).not.toHaveBeenCalled()
    })
})
