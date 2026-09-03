// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import ClubDetailsPage from './ClubDetailsPage.vue'

const { getClub, getPersons } = vi.hoisted(() => ({
    getClub: vi.fn(),
    getPersons: vi.fn(),
}))

vi.mock('../../api/clubs', () => ({ getClub }))
vi.mock('../../api/persons', () => ({ getPersons }))
vi.mock('../../api/users', () => ({ getUsers: vi.fn().mockResolvedValue([]) }))
vi.mock('../../api/ranks', () => ({ getRanks: vi.fn().mockResolvedValue([]) }))
vi.mock('../../stores/auth', () => ({
    useAuthStore: () => ({ isAuthenticated: false }),
}))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { id: '42' } }),
    useRouter: () => ({ push: vi.fn() }),
}))

describe('club details page', () => {
    it('loads persons and links both person names to the legacy page', async () => {
        getClub.mockResolvedValue({ id: '42', name: 'Клуб', personsCount: 1 })
        getPersons.mockResolvedValue({
            data: [
                {
                    id: '7',
                    lastname: 'Іваноў',
                    firstname: 'Ян',
                    birthday: '2001-06-04',
                },
            ],
            headers: {},
        })

        const wrapper = mount(ClubDetailsPage, {
            global: {
                stubs: {
                    RouterLink: { template: '<a><slot /></a>' },
                },
            },
        })
        await flushPromises()

        expect(getPersons).toHaveBeenCalledWith({
            clubId: 42,
            page: 1,
            perPage: 20,
        })
        expect(wrapper.findAll('a[href="/persons/7/show"]')).toHaveLength(2)
    })
})
