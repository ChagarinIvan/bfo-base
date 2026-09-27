// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import ImpressionDetails from './ImpressionDetails.vue'

const { auth, refreshUsers } = vi.hoisted(() => ({
    auth: { isAuthenticated: true },
    refreshUsers: vi.fn(),
}))

vi.mock('../stores/auth', () => ({ useAuthStore: () => auth }))
vi.mock('../api/users', () => ({ refreshUsers }))

const impression = { at: '2026-05-06T15:58:00+00:00', by: '5' }

describe('impression details', () => {
    beforeEach(() => {
        auth.isAuthenticated = true
        refreshUsers.mockReset()
    })

    it('shows the author email when a name is also present', () => {
        const wrapper = mount(ImpressionDetails, {
            props: {
                impression,
                users: [{ id: 5, name: 'Editor', email: 'editor@example.com' }],
            },
            global: { stubs: { Popover: true } },
        })

        expect(wrapper.text()).toContain('editor@example.com')
        expect(refreshUsers).not.toHaveBeenCalled()
    })

    it('refreshes a stale list before keeping the numeric fallback', async () => {
        refreshUsers.mockResolvedValue([
            { id: 5, name: 'Editor', email: 'editor@example.com' },
        ])
        const wrapper = mount(ImpressionDetails, {
            props: { impression, users: [] },
            global: { stubs: { Popover: true } },
        })

        await flushPromises()

        expect(refreshUsers).toHaveBeenCalledOnce()
        expect(wrapper.text()).toContain('editor@example.com')
    })

    it('keeps the fallback when the author is absent from the fresh list', async () => {
        refreshUsers.mockResolvedValue([])
        const wrapper = mount(ImpressionDetails, {
            props: { impression, users: [] },
            global: { stubs: { Popover: true } },
        })

        await flushPromises()

        expect(wrapper.text()).toContain('Карыстальнік №5')
    })

    it('does not fetch users for a guest', () => {
        auth.isAuthenticated = false
        mount(ImpressionDetails, {
            props: { impression, users: [] },
            global: { stubs: { Popover: true } },
        })

        expect(refreshUsers).not.toHaveBeenCalled()
    })
})
