// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it } from 'vitest'
import ListingTable from './ListingTable.vue'

describe('listing table', () => {
    beforeEach(() => localStorage.clear())

    it('persists selected columns and never permits removing the last one', async () => {
        const wrapper = mount(ListingTable, {
            props: {
                tableId: 'test-table',
                authenticated: false,
                columns: [
                    { key: 'name', label: 'Name', defaultVisible: true },
                    { key: 'date', label: 'Date', defaultVisible: true },
                ],
            },
            slots: { default: '<div>rows</div>' },
        })

        const inputs = wrapper.findAll('input[type="checkbox"]')
        await inputs[1].setValue(false)
        await inputs[0].setValue(false)

        expect(localStorage.getItem('bfo.table.test-table.guest')).toBe(
            JSON.stringify(['name']),
        )
    })

    it('renders an optional sticky filters slot', () => {
        const wrapper = mount(ListingTable, {
            props: {
                tableId: 'test-table',
                columns: [{ key: 'name', label: 'Name', defaultVisible: true }],
            },
            slots: {
                filters: '<div>filters</div>',
                default: '<div>rows</div>',
            },
        })

        expect(wrapper.find('.listing-table__filters--sticky').text()).toBe(
            'filters',
        )
    })

    it('renders declarative fields and delegates custom cells to named slots', () => {
        const wrapper = mount(ListingTable, {
            props: {
                tableId: 'test-table',
                columns: [
                    {
                        key: 'name',
                        label: 'Name',
                        field: 'name',
                        defaultVisible: true,
                    },
                    {
                        key: 'actions',
                        label: 'Actions',
                        defaultVisible: true,
                    },
                ],
                items: [{ id: '1', name: 'Group A' }],
            },
            slots: {
                'cell-actions': '<button>Open {{ data.id }}</button>',
            },
        })

        expect(wrapper.text()).toContain('Group A')
        expect(wrapper.get('button').text()).toBe('Open 1')
    })
})
