// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { defineComponent, h } from 'vue'
import { describe, expect, it, vi } from 'vitest'
import Menu from 'primevue/menu'
import { exportCupTable } from '../api/cups'
import { t } from '../i18n'
import CupInfoNavigation from './CupInfoNavigation.vue'
import ActionButton from './actions/ActionButton.vue'

const { route } = vi.hoisted(() => ({
    route: {
        params: { groupId: 'M_0_' as string | undefined },
        query: {},
    },
}))

vi.mock('../stores/auth', () => ({
    useAuthStore: () => ({ isAuthenticated: true }),
}))
vi.mock('vue-router', () => ({
    useRoute: () => route,
}))
vi.mock('../api/cups', () => ({
    exportCupTable: vi.fn(),
    clearCupCache: vi.fn(),
}))

const RouterLink = defineComponent({
    props: {
        to: { type: [String, Object], required: true },
        custom: { type: Boolean, default: false },
    },
    setup(_, { slots }) {
        return () =>
            h(
                'span',
                slots.default?.({
                    navigate: () => undefined,
                    isActive: false,
                    isExactActive: false,
                }),
            )
    },
})

describe('cup info navigation', () => {
    it('keeps edit, event, tab, export, cache, delete actions in order', () => {
        const wrapper = mount(CupInfoNavigation, {
            props: { cupId: '42', firstGroupId: 'M_0_' },
            global: {
                components: { RouterLink },
                stubs: {
                    ActionButton: {
                        props: ['label'],
                        template: '<button>{{ label }}</button>',
                    },
                },
            },
        })

        const labels = wrapper.findAll('button').map((button) => button.text())

        expect(labels).toEqual([
            'Рэдагаваць',
            'Дадаць этап',
            'Этапы',
            'Табліца',
            'Экспарт',
            'Абнавіць кэш',
            'Выдаліць',
        ])
    })

    it('opens a format menu and downloads the selected format', async () => {
        const wrapper = mount(CupInfoNavigation, {
            props: { cupId: '42', firstGroupId: 'M_0_' },
            global: { components: { RouterLink } },
        })
        const button = wrapper
            .findAll('button')
            .find((item) => item.text().includes('Экспарт'))
        expect(button?.attributes('aria-haspopup')).toBe('menu')

        await button?.trigger('click')
        expect(exportCupTable).not.toHaveBeenCalled()

        const menu = wrapper.findComponent(Menu)
        const items = menu.props('model') as Array<{
            label: string
            command: () => void
        }>
        expect(items.map((item) => item.label)).toEqual([
            'Excel (XLSX)',
            'HTML',
        ])

        vi.mocked(exportCupTable).mockResolvedValue({
            data: new Blob(['table']),
        } as Awaited<ReturnType<typeof exportCupTable>>)
        Object.defineProperty(URL, 'createObjectURL', {
            configurable: true,
            value: vi.fn(() => 'blob:table'),
        })
        Object.defineProperty(URL, 'revokeObjectURL', {
            configurable: true,
            value: vi.fn(),
        })
        const downloads: string[] = []
        const click = vi
            .spyOn(HTMLAnchorElement.prototype, 'click')
            .mockImplementation(function (this: HTMLAnchorElement) {
                downloads.push(this.download)
            })

        items[0]?.command()
        await flushPromises()
        items[1]?.command()
        await flushPromises()

        expect(exportCupTable).toHaveBeenCalledWith('42', 'xlsx', 'M_0_')
        expect(exportCupTable).toHaveBeenCalledWith('42', 'html', 'M_0_')
        expect(click).toHaveBeenCalledTimes(2)
        expect(downloads).toEqual(['cup-42-M_0_.xlsx', 'cup-42-M_0_.html'])

        vi.mocked(exportCupTable).mockRejectedValueOnce(
            new Error('download failed'),
        )
        items[1]?.command()
        await flushPromises()
        expect(wrapper.text()).toContain(t('spa.cups.error'))
        expect(
            wrapper
                .findAllComponents(ActionButton)
                .find((item) => item.props('label') === 'Экспарт')
                ?.props('loading'),
        ).toBe(false)
        click.mockRestore()
    })

    it('exports all groups from the cup events page', async () => {
        route.params.groupId = undefined
        vi.mocked(exportCupTable).mockClear()
        vi.mocked(exportCupTable).mockResolvedValue({
            data: new Blob(['table']),
        } as Awaited<ReturnType<typeof exportCupTable>>)
        Object.defineProperty(URL, 'createObjectURL', {
            configurable: true,
            value: vi.fn(() => 'blob:table'),
        })
        const click = vi
            .spyOn(HTMLAnchorElement.prototype, 'click')
            .mockImplementation(() => undefined)

        const wrapper = mount(CupInfoNavigation, {
            props: { cupId: '42', firstGroupId: 'M_0_' },
            global: { components: { RouterLink } },
        })
        const items = wrapper.findComponent(Menu).props('model') as Array<{
            command: () => void
        }>
        items[0]?.command()
        await flushPromises()

        expect(exportCupTable).toHaveBeenCalledWith('42', 'xlsx', undefined)
        click.mockRestore()
        route.params.groupId = 'M_0_'
    })

    it('disables export and shows loading until the download finishes', async () => {
        vi.mocked(exportCupTable).mockClear()
        let finish!: (value: Awaited<ReturnType<typeof exportCupTable>>) => void
        vi.mocked(exportCupTable).mockReturnValueOnce(
            new Promise((resolve) => {
                finish = resolve
            }),
        )
        Object.defineProperty(URL, 'createObjectURL', {
            configurable: true,
            value: vi.fn(() => 'blob:table'),
        })
        Object.defineProperty(URL, 'revokeObjectURL', {
            configurable: true,
            value: vi.fn(),
        })
        const click = vi
            .spyOn(HTMLAnchorElement.prototype, 'click')
            .mockImplementation(() => undefined)

        const wrapper = mount(CupInfoNavigation, {
            props: { cupId: '42', firstGroupId: 'M_0_' },
            global: { components: { RouterLink } },
        })
        const menu = wrapper.findComponent(Menu)
        const items = menu.props('model') as Array<{ command: () => void }>
        items[0]?.command()
        await wrapper.vm.$nextTick()

        const button = wrapper
            .findAllComponents(ActionButton)
            .find((item) => item.props('label') === 'Экспарт')
        expect(button?.props('loading')).toBe(true)
        expect(button?.props('disabled')).toBe(true)
        items[1]?.command()
        expect(exportCupTable).toHaveBeenCalledTimes(1)

        finish({ data: new Blob(['table']) } as Awaited<
            ReturnType<typeof exportCupTable>
        >)
        await flushPromises()
        expect(button?.props('loading')).toBe(false)
        expect(button?.props('disabled')).toBe(false)
        click.mockRestore()
    })
})
