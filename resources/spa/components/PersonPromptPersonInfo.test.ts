// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import PersonPromptPersonInfo from './PersonPromptPersonInfo.vue'
import type { Person } from '../api/types'

const { auth, getClubOptions, getPerson, getRanks, getUsers, replace } =
    vi.hoisted(() => ({
        auth: { isAuthenticated: true },
        getClubOptions: vi.fn(),
        getPerson: vi.fn(),
        getRanks: vi.fn(),
        getUsers: vi.fn(),
        replace: vi.fn(),
    }))

vi.mock('../api/clubs', () => ({ getClubOptions }))
vi.mock('../api/persons', () => ({ getPerson }))
vi.mock('../api/ranks', () => ({ getRanks }))
vi.mock('../api/users', () => ({ getUsers }))
vi.mock('../stores/auth', () => ({
    useAuthStore: () => auth,
}))
vi.mock('vue-router', () => ({
    useRouter: () => ({ replace }),
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

function person(
    id: string,
    lastname: string,
    birthday: string | null = null,
): Person {
    return {
        id,
        lastname,
        firstname: 'Runner',
        birthday,
        rankId: 1,
        citizenship: 'belarus',
        clubId: null,
    }
}

function clubOption(id: string, name: string) {
    return { id, name }
}

const routerLinkStub = {
    props: ['to'],
    template: '<a :href="to"><slot /></a>',
}

describe('person prompt person info', () => {
    beforeEach(() => {
        vi.resetAllMocks()
        auth.isAuthenticated = true
        getRanks.mockResolvedValue([])
        getClubOptions.mockResolvedValue([])
        getUsers.mockResolvedValue([])
    })

    function mountCard() {
        return mount(PersonPromptPersonInfo, {
            props: { personId: '1' },
            global: {
                stubs: {
                    Card: {
                        template:
                            '<div><slot name="title" /><slot name="content" /></div>',
                    },
                    ImpressionDetails: true,
                    Message: { template: '<div><slot /></div>' },
                    RouterLink: routerLinkStub,
                },
            },
        })
    }

    it.each([
        ['unknown', 'Невядома'],
        ['manual', 'Ручны пераразлік'],
        ['protocol', 'Апрацоўка пратакола'],
        ['event_results_processed', 'Апрацоўка вынікаў этапу'],
        ['event_results_cleared', 'Ачыстка вынікаў этапу'],
        ['protocol_person_changed', 'Змена прывязкі вынікаў да спартсмена'],
        ['daily', 'Штодзённая праверка'],
        ['refill', 'Поўны пераразлік'],
        ['manual_rank_added', 'Дададзена ручное выкананне'],
        ['manual_rank_removed', 'Выдалена ручное выкананне'],
    ] as const)(
        'shows calculation time and %s below the current rank for authenticated users',
        async (reason, label) => {
            getPerson.mockResolvedValue({
                ...person('1', 'Runner'),
                rankCalculatedAt: '2026-11-06T12:34:56+00:00',
                rankCalculationReason: reason,
            })
            const wrapper = mountCard()
            await flushPromises()

            const rows = wrapper.findAll('tr')
            expect(rows[3].text()).toContain('Разрад')
            expect(rows[4].text()).toContain('Апошні пераразлік разраду')
            expect(rows[4].text()).toContain('2026-11-06 12:34:56')
            expect(rows[4].get('time').attributes('datetime')).toBe(
                '2026-11-06T12:34:56+00:00',
            )
            expect(rows[5].text()).toContain('Прычына пераразліку')
            expect(rows[5].text()).toContain(label)
        },
    )

    it('shows unknown metadata before the first calculation', async () => {
        getPerson.mockResolvedValue({
            ...person('1', 'Runner'),
            rankCalculatedAt: null,
            rankCalculationReason: 'unknown',
        })
        const wrapper = mountCard()
        await flushPromises()

        const rows = wrapper.findAll('tr')
        expect(rows[4].text()).toContain('Невядома')
        expect(rows[5].text()).toContain('Невядома')
        expect(wrapper.find('time').exists()).toBe(false)
    })

    it('hides calculation metadata from guests even when supplied by the API', async () => {
        auth.isAuthenticated = false
        getPerson.mockResolvedValue({
            ...person('1', 'Runner'),
            rankCalculatedAt: '2026-11-06T12:34:56+00:00',
            rankCalculationReason: 'manual',
        })
        const wrapper = mountCard()
        await flushPromises()

        expect(wrapper.text()).not.toContain('Апошні пераразлік разраду')
        expect(wrapper.text()).not.toContain('Прычына пераразліку')
        expect(wrapper.text()).not.toContain('2026-11-06')
        expect(wrapper.text()).not.toContain('Ручны пераразлік')
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
                    RouterLink: routerLinkStub,
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
                    RouterLink: routerLinkStub,
                },
            },
        })
        await flushPromises()

        expect(wrapper.text()).not.toContain('Створана')
        expect(wrapper.text()).not.toContain('Зменена')
    })

    it('shows only the person birth year', async () => {
        getPerson.mockResolvedValue(person('1', 'Runner', '2001-06-04'))

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
                    RouterLink: routerLinkStub,
                },
            },
        })
        await flushPromises()

        expect(wrapper.text()).toContain('2001')
        expect(wrapper.text()).not.toContain('2001-06-04')
    })

    it('links the person club to its details page', async () => {
        getPerson.mockResolvedValue({ ...person('1', 'Member'), clubId: '7' })
        getClubOptions.mockResolvedValue([clubOption('7', 'Orienteering Club')])

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
                    RouterLink: routerLinkStub,
                },
            },
        })
        await flushPromises()

        expect(wrapper.get('a').attributes('href')).toBe('/app/clubs/7')
        expect(wrapper.get('a').text()).toBe('Orienteering Club')
    })

    it('shows the standard not-found page when the person does not exist', async () => {
        getPerson.mockRejectedValue({ response: { status: 404 } })

        mount(PersonPromptPersonInfo, {
            props: { personId: 'missing' },
            global: {
                stubs: {
                    Card: true,
                    ImpressionDetails: true,
                    Message: true,
                    RouterLink: routerLinkStub,
                },
            },
        })
        await flushPromises()

        expect(replace).toHaveBeenCalledWith({ name: 'not-found' })
    })
})
