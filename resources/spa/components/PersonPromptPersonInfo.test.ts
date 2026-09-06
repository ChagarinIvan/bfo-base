// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import PersonPromptPersonInfo from './PersonPromptPersonInfo.vue'
import type { Person } from '../api/types'

const { auth, getClubOptions, getPerson, getRanks, getUsers } = vi.hoisted(
    () => ({
        auth: { isAuthenticated: true },
        getClubOptions: vi.fn(),
        getPerson: vi.fn(),
        getRanks: vi.fn(),
        getUsers: vi.fn(),
    }),
)

vi.mock('../api/clubs', () => ({ getClubOptions }))
vi.mock('../api/persons', () => ({ getPerson }))
vi.mock('../api/ranks', () => ({ getRanks }))
vi.mock('../api/users', () => ({ getUsers }))
vi.mock('../stores/auth', () => ({
    useAuthStore: () => auth,
}))

function deferred<T>(): {
    promise: Promise<T>
    resolve: (value: T) => void
} {
    let resolve!: (value: T) => void
    const promise = new Promise<T>((nextResolve) => {
        resolve = nextResolve
    })

    return { promise, resolve }
}

function person(id: string, lastname: string): Person {
    return {
        id,
        lastname,
        firstname: 'Runner',
        birthday: null,
        rankId: 1,
        clubId: null,
    }
}

describe('person prompt person info', () => {
    beforeEach(() => {
        vi.resetAllMocks()
        auth.isAuthenticated = true
        getRanks.mockResolvedValue([])
        getClubOptions.mockResolvedValue([])
        getUsers.mockResolvedValue([])
    })

    it('does not let an older person response overwrite the current one', async () => {
        const first = deferred<Person>()
        const second = deferred<Person>()
        getPerson.mockImplementation((id: string) =>
            id === '1' ? first.promise : second.promise,
        )

        const wrapper = mount(PersonPromptPersonInfo, {
            props: { personId: '1' },
            global: {
                stubs: {
                    Card: {
                        template:
                            '<div><slot name="title" /><slot name="content" /></div>',
                    },
                    ImpressionDetails: true,
                    Message: { template: '<div><slot /></div>' },
                },
            },
        })

        await wrapper.setProps({ personId: '2' })
        second.resolve(person('2', 'Current'))
        await flushPromises()
        first.resolve(person('1', 'Stale'))
        await flushPromises()

        expect(wrapper.text()).toContain('Current')
        expect(wrapper.text()).not.toContain('Stale')
    })

    it('hides impression rows for anonymous visitors', async () => {
        auth.isAuthenticated = false
        getPerson.mockResolvedValue(person('1', 'Public'))

        const wrapper = mount(PersonPromptPersonInfo, {
            props: { personId: '1' },
            global: {
                stubs: {
                    Card: {
                        template:
                            '<div><slot name="title" /><slot name="content" /></div>',
                    },
                    ImpressionDetails: true,
                    Message: { template: '<div><slot /></div>' },
                },
            },
        })
        await flushPromises()

        expect(wrapper.text()).not.toContain('Створана')
        expect(wrapper.text()).not.toContain('Зменена')
    })
})
