// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import EditPersonPage from './EditPersonPage.vue'

const { getClubOptions, getPerson, push, updatePerson } = vi.hoisted(() => ({
    getClubOptions: vi.fn(),
    getPerson: vi.fn(),
    push: vi.fn(),
    updatePerson: vi.fn(),
}))

vi.mock('../../api/clubs', () => ({ getClubOptions }))
vi.mock('../../api/persons', () => ({ getPerson, updatePerson }))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { personId: '42' } }),
    useRouter: () => ({ push }),
}))

describe('edit person page', () => {
    beforeEach(() => {
        vi.resetAllMocks()
    })

    it('shows not found only when the person request returns 404', async () => {
        getPerson.mockRejectedValue({ response: { status: 404 } })
        getClubOptions.mockResolvedValue([])

        const wrapper = mount(EditPersonPage, {
            global: { plugins: [PrimeVue] },
        })
        await flushPromises()

        expect(wrapper.find('.p-message').text()).toBe(
            'Удзельнік не знойдзены.',
        )
    })

    it('shows a generic load error when club options cannot be loaded', async () => {
        getPerson.mockResolvedValue({
            id: '42',
            lastname: 'Іваноў',
            firstname: 'Ян',
            birthday: null,
            clubId: null,
            citizenship: 'belarus',
        })
        getClubOptions.mockRejectedValue(new Error('unavailable'))

        const wrapper = mount(EditPersonPage, {
            global: { plugins: [PrimeVue] },
        })
        await flushPromises()

        expect(wrapper.find('.p-message').text()).toBe(
            'Не атрымалася загрузіць удзельнікаў.',
        )
    })

    it('refreshes the person card after a successful update', async () => {
        getPerson.mockResolvedValue({
            id: '42',
            lastname: 'Іваноў',
            firstname: 'Ян',
            birthday: null,
            clubId: null,
            citizenship: 'belarus',
        })
        getClubOptions.mockResolvedValue([])
        updatePerson.mockResolvedValue({ id: '42' })

        const wrapper = mount(EditPersonPage, {
            global: { plugins: [PrimeVue] },
        })
        await flushPromises()
        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(push).toHaveBeenCalledWith({
            path: '/app/persons/42',
            query: { refresh: expect.any(String) },
        })
    })
})
