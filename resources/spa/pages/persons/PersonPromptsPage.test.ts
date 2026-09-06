// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import PersonPromptsPage from './PersonPromptsPage.vue'

const { auth, getPersonPrompts, getUsers, push } = vi.hoisted(() => ({
    auth: { isAuthenticated: true },
    getPersonPrompts: vi.fn(),
    getUsers: vi.fn(),
    push: vi.fn(),
}))

vi.mock('../../api/personPrompts', () => ({ getPersonPrompts }))
vi.mock('../../api/users', () => ({ getUsers }))
vi.mock('../../stores/auth', () => ({ useAuthStore: () => auth }))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { personId: '7' } }),
    useRouter: () => ({ push }),
}))

describe('person prompts page', () => {
    beforeEach(() => {
        vi.resetAllMocks()
        auth.isAuthenticated = true
        getUsers.mockResolvedValue([])
    })

    it('shows impression columns for authenticated visitors', async () => {
        getPersonPrompts.mockResolvedValue({
            data: [
                {
                    id: '11',
                    personId: '7',
                    prompt: 'Runner',
                    metaphone: 'RNR',
                },
            ],
            headers: { 'x-pagination-total': '1' },
        })

        const wrapper = mount(PersonPromptsPage, {
            global: {
                stubs: {
                    Button: true,
                    Column: {
                        props: ['header'],
                        template: '<div class="column">{{ header }}</div>',
                    },
                    ConfirmDeleteDialog: true,
                    DataTable: { template: '<div><slot /></div>' },
                    ImpressionDetails: true,
                    Message: { template: '<div><slot /></div>' },
                    Paginator: true,
                    PersonPromptActionMenu: true,
                },
            },
        })
        await flushPromises()

        expect(getUsers).toHaveBeenCalledOnce()
        expect(wrapper.findAll('.column')).toHaveLength(5)
        expect(wrapper.text()).toContain('Створана')
        expect(wrapper.text()).toContain('Зменена')
    })
})
