// @vitest-environment happy-dom

import { flushPromises, mount } from '@vue/test-utils'
import { h } from 'vue'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import ListingTable from './ListingTable.vue'

describe('listing table sticky headers', () => {
    const frames = new Map<number, FrameRequestCallback>()
    let resized: ResizeObserverCallback
    let disconnect: ReturnType<typeof vi.fn>

    function flushFrame(): void {
        const callbacks = [...frames.values()]
        frames.clear()
        callbacks.forEach((callback) => callback(0))
    }

    beforeEach(() => {
        localStorage.clear()
        frames.clear()
        let id = 0
        vi.spyOn(window, 'requestAnimationFrame').mockImplementation(
            (callback) => {
                frames.set(++id, callback)
                return id
            },
        )
        vi.spyOn(window, 'cancelAnimationFrame').mockImplementation((frame) => {
            frames.delete(frame)
        })
        disconnect = vi.fn()
        vi.stubGlobal(
            'ResizeObserver',
            class {
                observe = vi.fn()
                disconnect = disconnect
                constructor(callback: ResizeObserverCallback) {
                    resized = callback
                }
            },
        )
    })

    afterEach(() => {
        document.body.innerHTML = ''
        vi.restoreAllMocks()
        vi.unstubAllGlobals()
    })

    it('tracks the controls height, clamps at the table end and releases its listeners', () => {
        const headerAction = vi.fn()
        const wrapper = mount(ListingTable, {
            attachTo: document.body,
            props: {
                tableId: 'sticky-header',
                columns: [
                    {
                        key: 'name',
                        field: 'name',
                        label: 'Name',
                        defaultVisible: true,
                    },
                ],
                items: [{ name: 'Participant' }],
            },
            slots: {
                filters: '<div>Filters</div>',
                'header-name': () =>
                    h('button', { onClick: headerAction }, 'Header action'),
            },
        })
        try {
            const controls = wrapper.get('.listing-table__controls')
                .element as HTMLElement
            const table = wrapper.get('table').element
            const header = wrapper.get('thead').element as HTMLElement
            let controlsHeight = 124
            let tableTop = -100
            controls.style.top = '76px'
            vi.spyOn(controls, 'getBoundingClientRect').mockImplementation(
                () => new DOMRect(0, 76, 600, controlsHeight),
            )
            vi.spyOn(table, 'getBoundingClientRect').mockImplementation(
                () => new DOMRect(0, tableTop, 600, 1000),
            )
            vi.spyOn(header, 'getBoundingClientRect').mockImplementation(
                () =>
                    new DOMRect(
                        0,
                        tableTop +
                            (header.classList.contains(
                                'listing-table__header--sticky',
                            )
                                ? Number.parseFloat(
                                      header.style.getPropertyValue(
                                          '--listing-table-header-offset',
                                      ) || '0',
                                  )
                                : 0),
                        600,
                        40,
                    ),
            )
            flushFrame()

            expect(
                header.style.getPropertyValue('--listing-table-header-offset'),
            ).toBe('300px')
            expect(header.getBoundingClientRect().top).toBe(
                controls.getBoundingClientRect().bottom,
            )
            expect(wrapper.get('thead button').text()).toBe('Header action')
            wrapper
                .get('thead button')
                .element.dispatchEvent(new MouseEvent('click'))
            expect(headerAction).toHaveBeenCalledOnce()

            header.classList.remove('listing-table__header--sticky')
            window.dispatchEvent(new Event('scroll'))
            flushFrame()
            expect(
                header.classList.contains('listing-table__header--sticky'),
            ).toBe(true)
            expect(
                header.style.getPropertyValue('--listing-table-header-offset'),
            ).toBe('300px')

            window.dispatchEvent(new Event('scroll'))
            window.dispatchEvent(new Event('scroll'))
            expect(frames.size).toBe(1)
            flushFrame()
            expect(
                header.style.getPropertyValue('--listing-table-header-offset'),
            ).toBe('300px')

            controlsHeight = 224
            resized([], {} as ResizeObserver)
            flushFrame()
            expect(
                header.style.getPropertyValue('--listing-table-header-offset'),
            ).toBe('400px')
            expect(wrapper.get('.listing-table').attributes('style')).toContain(
                '--listing-table-scroll-offset: 356px',
            )

            tableTop = -1100
            window.dispatchEvent(new Event('scroll'))
            flushFrame()
            expect(
                header.style.getPropertyValue('--listing-table-header-offset'),
            ).toBe('960px')

            tableTop = 300
            window.dispatchEvent(new Event('scroll'))
            flushFrame()
            expect(
                header.style.getPropertyValue('--listing-table-header-offset'),
            ).toBe('0px')
            window.dispatchEvent(new Event('scroll'))
            expect(frames.size).toBe(1)
        } finally {
            wrapper.unmount()
        }

        expect(disconnect).toHaveBeenCalledOnce()
        window.dispatchEvent(new Event('scroll'))
        expect(frames.size).toBe(0)
    })

    it('discovers a table inserted asynchronously through the default slot', async () => {
        const wrapper = mount(ListingTable, {
            props: {
                tableId: 'slot-header',
                columns: [{ key: 'name', label: 'Name', defaultVisible: true }],
            },
            slots: { default: '<div class="slot-table" />' },
        })
        try {
            flushFrame()
            const host = wrapper.get('.slot-table').element
            host.innerHTML =
                '<table><thead class="p-datatable-thead"><tr><th>Name</th></tr></thead><tbody><tr><td>Participant</td></tr></tbody></table>'
            await flushPromises()
            flushFrame()

            expect(wrapper.get('thead').classes()).toContain(
                'listing-table__header--sticky',
            )
        } finally {
            wrapper.unmount()
        }
    })
})
