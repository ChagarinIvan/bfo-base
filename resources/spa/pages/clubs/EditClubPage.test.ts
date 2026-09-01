// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import PrimeVue from 'primevue/config'
import EditClubPage from './EditClubPage.vue'

const { getClub, updateClub, push } = vi.hoisted(() => ({
    getClub: vi.fn(),
    updateClub: vi.fn(),
    push: vi.fn(),
}))

vi.mock('../../api/clubs', () => ({ getClub, updateClub }))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { id: '42' } }),
    useRouter: () => ({ push }),
}))
vi.mock('primevue/usetoast', () => ({ useToast: () => ({ add: vi.fn() }) }))

describe('edit club page', () => {
    it('prefills the form and navigates after update', async () => {
        getClub.mockResolvedValue({ id: '42', name: 'Стары клуб' })
        updateClub.mockResolvedValue({ id: '42', name: 'Новы клуб' })

        const wrapper = mount(EditClubPage, { global: { plugins: [PrimeVue] } })
        await flushPromises()

        expect(
            (wrapper.find('#club-name').element as HTMLInputElement).value,
        ).toBe('Стары клуб')
        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(updateClub).toHaveBeenCalledWith('42', { name: 'Стары клуб' })
        expect(push).toHaveBeenCalledWith('/app/clubs/42')
    })

    it('distinguishes a general load error from not found', async () => {
        getClub.mockRejectedValue({
            isAxiosError: true,
            response: { status: 500 },
        })

        const wrapper = mount(EditClubPage, { global: { plugins: [PrimeVue] } })
        await flushPromises()

        expect(wrapper.text()).toContain('Не атрымалася змяніць клуб.')
    })
})
