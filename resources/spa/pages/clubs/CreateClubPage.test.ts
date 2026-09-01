// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import PrimeVue from 'primevue/config'
import CreateClubPage from './CreateClubPage.vue'

const { createClub, push } = vi.hoisted(() => ({
    createClub: vi.fn(),
    push: vi.fn(),
}))

vi.mock('../../api/clubs', () => ({ createClub }))
vi.mock('vue-router', () => ({ useRouter: () => ({ push }) }))
vi.mock('primevue/usetoast', () => ({ useToast: () => ({ add: vi.fn() }) }))

describe('create club page', () => {
    it('navigates to the created club', async () => {
        createClub.mockResolvedValue({ id: '42', name: 'Новы клуб' })

        const wrapper = mount(CreateClubPage, {
            global: { plugins: [PrimeVue] },
        })
        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(createClub).toHaveBeenCalledWith({ name: '' })
        expect(push).toHaveBeenCalledWith('/app/clubs/42')
    })

    it('keeps validation on the name field and shows conflicts as form errors', async () => {
        createClub.mockRejectedValueOnce({
            isAxiosError: true,
            response: {
                status: 422,
                data: { errors: [{ field: 'name', message: 'Required' }] },
            },
        })
        const wrapper = mount(CreateClubPage, {
            global: { plugins: [PrimeVue] },
        })
        await wrapper.find('form').trigger('submit')
        await flushPromises()
        expect(wrapper.find('.field-error').text()).toBe('Required')
        expect(wrapper.find('.p-message').exists()).toBe(false)

        createClub.mockRejectedValueOnce({
            isAxiosError: true,
            response: {
                status: 409,
                data: {
                    errors: [
                        {
                            code: 'club_name_already_exists',
                            message: 'Duplicate',
                        },
                    ],
                },
            },
        })
        await wrapper.find('form').trigger('submit')
        await flushPromises()
        expect(wrapper.find('.p-message').text()).toContain(
            'Клуб з такой назвай ужо існуе.',
        )
    })
})
