// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { reactive } from 'vue'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import AppLayout from './AppLayout.vue'
import {
    authenticatedAccountNavigation,
    authenticatedCompetitionNavigation,
    authenticatedPersonsNavigation,
    competitionNavigation,
    personsNavigation,
} from './navigationModels'

const { auth } = vi.hoisted(() => ({
    auth: {
        isAuthenticated: false,
        canAccessHorizon: false,
        logout: vi.fn(),
        openHorizon: vi.fn(),
    },
}))

vi.mock('../stores/auth', () => ({ useAuthStore: () => reactive(auth) }))
vi.mock('vue-router', () => ({
    RouterLink: {
        props: ['to'],
        template: '<a :href="to"><slot /></a>',
    },
    useRouter: () => ({ push: vi.fn() }),
}))
vi.mock('primevue/usetoast', () => ({ useToast: () => ({ add: vi.fn() }) }))

function mountLayout() {
    return mount(AppLayout, {
        global: {
            plugins: [createPinia()],
            stubs: {
                RouterLink: {
                    props: ['to'],
                    template: '<a :href="to"><slot /></a>',
                },
            },
        },
    })
}

beforeEach(() => {
    setActivePinia(createPinia())
    localStorage.clear()
    document.documentElement.classList.remove('app-night-mode')
    auth.isAuthenticated = false
    auth.canAccessHorizon = false
    auth.logout.mockReset()
    auth.openHorizon.mockReset()
})

describe('hybrid SPA navbar', () => {
    it('shows the appearance control for anonymous visitors', () => {
        const wrapper = mountLayout()
        const toggle = wrapper.get('.app-appearance-toggle')

        expect(toggle.attributes('aria-pressed')).toBe('false')
        expect(toggle.attributes('aria-label')).toBe('Уключыць начны рэжым')
        expect(
            toggle.find('.app-appearance-toggle__thumb .pi-moon').exists(),
        ).toBe(true)
        expect(wrapper.find('.app-login-link').exists()).toBe(true)
    })

    it('keeps Horizon and logout actions beside the appearance control', async () => {
        auth.isAuthenticated = true
        auth.canAccessHorizon = true
        const wrapper = mountLayout()
        const toggle = wrapper.get('.app-appearance-toggle')

        expect(toggle.element.parentElement?.firstElementChild).toBe(
            toggle.element,
        )
        expect(wrapper.find('button.app-logout-button').exists()).toBe(true)
        expect(toggle.attributes('title')).toBe('Светлы рэжым уключаны')

        await toggle.trigger('keydown', { key: 'Enter' })
        await toggle.trigger('click')

        expect(toggle.attributes('aria-pressed')).toBe('true')
        expect(toggle.attributes('aria-label')).toBe('Уключыць светлы рэжым')
        expect(toggle.attributes('title')).toBe('Начны рэжым уключаны')
        expect(
            toggle.find('.app-appearance-toggle__thumb .pi-sun').exists(),
        ).toBe(true)
    })

    it('keeps migrated competitions and groups in SPA', () => {
        expect(competitionNavigation).toEqual([
            {
                label: 'spa.nav.competitions',
                href: '/app/competitions',
                spa: true,
            },
            {
                label: 'spa.nav.groups',
                href: '/app/groups',
                spa: true,
            },
            { label: 'spa.nav.cups', href: '/app/cups', spa: true },
        ])
        expect(personsNavigation.map((item) => item.href)).toEqual([
            '/app/persons',
            '/app/clubs',
        ])
        expect(personsNavigation[1].spa).toBe(true)
    })

    it('places rank checks in the authenticated persons menu', () => {
        expect(authenticatedCompetitionNavigation).toEqual([])
        expect(authenticatedPersonsNavigation.map((item) => item.href)).toEqual(
            ['/app/rank-checks'],
        )
        expect(authenticatedAccountNavigation.map((item) => item.href)).toEqual(
            ['/app/registration'],
        )
        expect(
            [
                ...competitionNavigation,
                ...personsNavigation,
                ...authenticatedPersonsNavigation,
                ...authenticatedCompetitionNavigation,
                ...authenticatedAccountNavigation,
            ].map((item) => item.href),
        ).not.toEqual(expect.arrayContaining(['/faq', '/faq/api']))
    })
})
