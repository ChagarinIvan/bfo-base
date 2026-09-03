// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import PersonsPage from './PersonsPage.vue'

const { getClubOptions, getPersons, getRanks, getUsers } = vi.hoisted(() => ({
    getClubOptions: vi.fn(),
    getPersons: vi.fn(),
    getRanks: vi.fn(),
    getUsers: vi.fn(),
}))

vi.mock('../../api/clubs', () => ({ getClubOptions }))
vi.mock('../../api/persons', () => ({ getPersons }))
vi.mock('../../api/ranks', () => ({ getRanks }))
vi.mock('../../api/users', () => ({ getUsers }))
vi.mock('../../stores/auth', () => ({
    useAuthStore: () => ({ isAuthenticated: true }),
}))

describe('persons page', () => {
    beforeEach(() => {
        vi.resetAllMocks()
    })

    it('loads the shared list with filters and exposes the legacy create action', async () => {
        getClubOptions.mockResolvedValue([{ id: '7', name: 'Клуб' }])
        getRanks.mockResolvedValue([{ id: 6, label: 'I' }])
        getUsers.mockResolvedValue([])
        getPersons.mockResolvedValue({
            data: [
                {
                    id: '7',
                    lastname: 'Іваноў',
                    firstname: 'Ян',
                    birthday: '2001-06-04',
                    rankId: 6,
                    clubId: '7',
                },
            ],
            headers: {},
        })

        const wrapper = mount(PersonsPage, {
            global: {
                stubs: {
                    Button: true,
                    Card: { template: '<div><slot name="content" /></div>' },
                    InputText: true,
                    Message: true,
                    Paginator: true,
                    PersonTable: true,
                    Select: true,
                    Toolbar: { template: '<div><slot name="end" /></div>' },
                },
            },
        })
        await flushPromises()

        expect(getPersons).toHaveBeenCalledWith({
            page: 1,
            perPage: 20,
        })
        expect(wrapper.find('a[href="/persons/create"]').exists()).toBe(true)
    })

    it('retries the complete initialization when loading options fails', async () => {
        getClubOptions
            .mockRejectedValueOnce(new Error('options unavailable'))
            .mockResolvedValue([{ id: '7', name: 'Клуб' }])
        getRanks.mockResolvedValue([{ id: 6, label: 'I' }])
        getUsers.mockResolvedValue([])
        getPersons.mockResolvedValue({ data: [], headers: {} })

        const wrapper = mount(PersonsPage, {
            global: {
                stubs: {
                    Button: {
                        props: ['label'],
                        template: '<button type="button">{{ label }}</button>',
                    },
                    Card: { template: '<div><slot name="content" /></div>' },
                    InputText: true,
                    Message: { template: '<div><slot /></div>' },
                    Paginator: true,
                    PersonTable: true,
                    Select: true,
                    Toolbar: { template: '<div><slot name="end" /></div>' },
                },
            },
        })
        await flushPromises()

        expect(getPersons).not.toHaveBeenCalled()
        const initialClubOptionsCalls = getClubOptions.mock.calls.length
        const initialRankCalls = getRanks.mock.calls.length
        const retryButton = wrapper
            .findAll('button')
            .find((button) => button.text() === 'Паўтарыць')
        expect(retryButton).toBeDefined()

        await retryButton?.trigger('click')
        await flushPromises()

        expect(getClubOptions.mock.calls.length).toBe(
            initialClubOptionsCalls + 1,
        )
        expect(getRanks.mock.calls.length).toBe(initialRankCalls + 1)
        expect(getPersons).toHaveBeenCalledOnce()
    })
})
