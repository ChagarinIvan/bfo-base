// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
import { describe, expect, it, vi } from 'vitest'
import CreateCupPage from './CreateCupPage.vue'

const { createCup, push, add } = vi.hoisted(() => ({
    createCup: vi.fn(),
    push: vi.fn(),
    add: vi.fn(),
}))

vi.mock('../../api/cups', () => ({ createCup }))
vi.mock('../../api/years', () => ({
    getYears: vi.fn().mockResolvedValue([2026, 2025]),
}))
vi.mock('vue-router', () => ({ useRouter: () => ({ push }) }))
vi.mock('primevue/usetoast', () => ({ useToast: () => ({ add }) }))

describe('create cup page', () => {
    it('submits the form and returns to the listing', async () => {
        createCup.mockResolvedValue({ id: '42' })
        const wrapper = mount(CreateCupPage, {
            global: { plugins: [PrimeVue] },
        })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(createCup).toHaveBeenCalledWith({
            name: '',
            eventsCount: 1,
            year: new Date().getFullYear(),
            type: 'master',
            visible: true,
        })
        expect(add).toHaveBeenCalled()
        expect(push).toHaveBeenCalledWith('/app/cups')
    })
})
