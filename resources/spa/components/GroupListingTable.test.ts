// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import GroupListingTable from './GroupListingTable.vue'

describe('group listing table', () => {
    it('forwards rows and pagination to the shared listing table', () => {
        const groups = [{ id: '1', name: 'Elite', distancesCount: 2 }]
        const pagination = {
            currentPage: 1,
            perPage: 20,
            total: 1,
            lastPage: 1,
        }
        const ListingTable = {
            props: ['items', 'pagination'],
            template: '<div />',
        }

        const wrapper = mount(GroupListingTable, {
            props: {
                groups,
                name: '',
                loading: false,
                error: '',
                pagination,
                showActions: false,
            },
            global: {
                stubs: {
                    ListingTable,
                    RouterLink: { template: '<a><slot /></a>' },
                },
            },
        })

        expect(
            wrapper.findComponent(ListingTable).props('items'),
        ).toStrictEqual(groups)
        expect(
            wrapper.findComponent(ListingTable).props('pagination'),
        ).toStrictEqual(pagination)
    })
})
