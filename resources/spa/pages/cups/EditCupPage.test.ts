// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
import { describe, expect, it, vi } from 'vitest'
import EditCupPage from './EditCupPage.vue'

const { getCup, updateCup, push } = vi.hoisted(() => ({
    getCup: vi.fn(),
    updateCup: vi.fn(),
    push: vi.fn(),
}))

vi.mock('../../api/cups', () => ({ getCup, updateCup }))
vi.mock('../../api/years', () => ({
    getYears: vi.fn().mockResolvedValue([2026, 2025]),
}))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { id: '42' } }),
    useRouter: () => ({ push }),
}))
vi.mock('primevue/usetoast', () => ({ useToast: () => ({ add: vi.fn() }) }))

describe('cup edit page', () => {
    it('loads prefilled values and submits an update', async () => {
        getCup.mockResolvedValue({
            id: '42',
            name: 'Existing cup',
            eventsCount: '4',
            year: 2025,
            type: 'master',
            groups: [],
            visible: true,
        })
        updateCup.mockResolvedValue({ id: '42' })
        const wrapper = mount(EditCupPage, {
            global: { plugins: [PrimeVue] },
        })

        await flushPromises()
        expect(
            (wrapper.find('#cup-name').element as HTMLInputElement).value,
        ).toBe('Existing cup')

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(updateCup).toHaveBeenCalledWith('42', {
            name: 'Existing cup',
            eventsCount: 4,
            year: 2025,
            type: 'master',
            visible: true,
        })
        expect(push).toHaveBeenCalledWith('/app/cups')
    })

    it('shows a not-found error without rendering the form', async () => {
        getCup.mockRejectedValue({
            isAxiosError: true,
            response: { status: 404 },
        })
        const wrapper = mount(EditCupPage, {
            global: { plugins: [PrimeVue] },
        })

        await flushPromises()

        expect(wrapper.find('form').exists()).toBe(false)
        expect(wrapper.find('.p-message').text()).toContain('не знойдзены')
    })
})
