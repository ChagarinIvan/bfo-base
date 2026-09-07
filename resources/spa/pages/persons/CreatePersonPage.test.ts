// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
import { describe, expect, it, vi } from 'vitest'
import CreatePersonPage from './CreatePersonPage.vue'

const { createPerson, getClubOptions, push } = vi.hoisted(() => ({
    createPerson: vi.fn(),
    getClubOptions: vi.fn(),
    push: vi.fn(),
}))

vi.mock('../../api/clubs', () => ({ getClubOptions }))
vi.mock('../../api/persons', () => ({ createPerson }))
vi.mock('vue-router', () => ({ useRouter: () => ({ push }) }))

describe('create person page', () => {
    it('creates a person through the shared form and opens its SPA card', async () => {
        getClubOptions.mockResolvedValue([])
        createPerson.mockResolvedValue({ id: '42' })

        const wrapper = mount(CreatePersonPage, {
            global: { plugins: [PrimeVue] },
        })
        await flushPromises()
        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(createPerson).toHaveBeenCalledWith({
            lastname: '',
            firstname: '',
            birthday: null,
            clubId: null,
            citizenship: 'belarus',
        })
        expect(push).toHaveBeenCalledWith('/app/persons/42')
    })

    it('shows an error instead of an unhandled rejection when club options fail to load', async () => {
        getClubOptions.mockRejectedValue(new Error('unavailable'))

        const wrapper = mount(CreatePersonPage, {
            global: { plugins: [PrimeVue] },
        })
        await flushPromises()

        expect(wrapper.find('.p-message').text()).toBe(
            'Не атрымалася загрузіць удзельнікаў.',
        )
        expect(wrapper.find('form').exists()).toBe(false)
    })
})
