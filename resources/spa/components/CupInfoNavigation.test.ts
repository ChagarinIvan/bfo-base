// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { defineComponent, h } from 'vue'
import { describe, expect, it, vi } from 'vitest'
import Menu from 'primevue/menu'
import { exportCupTable } from '../api/cups'
import { t } from '../i18n'
import CupInfoNavigation from './CupInfoNavigation.vue'

vi.mock('../stores/auth', () => ({
    useAuthStore: () => ({ isAuthenticated: true }),
}))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { groupId: 'M_0_' }, query: {} }),
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
        expect(items.map((item) => item.label)).toEqual(['CSV', 'HTML'])

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
        items[1]?.command()
        await flushPromises()

        expect(exportCupTable).toHaveBeenCalledWith('42', 'csv')
        expect(exportCupTable).toHaveBeenCalledWith('42', 'html')
        expect(click).toHaveBeenCalledTimes(2)
        expect(downloads).toEqual(['cup-42.csv', 'cup-42.html'])

        vi.mocked(exportCupTable).mockRejectedValueOnce(
            new Error('download failed'),
        )
        items[1]?.command()
        await flushPromises()
        expect(wrapper.text()).toContain(t('spa.cups.error'))
        click.mockRestore()
    })
})
