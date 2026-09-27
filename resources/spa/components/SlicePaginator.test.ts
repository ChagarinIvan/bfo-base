// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
import Paginator from 'primevue/paginator'
import { describe, expect, it } from 'vitest'
import SlicePaginator from './SlicePaginator.vue'

describe('slice paginator', () => {
    it('mimics total records from the probe-row pagination metadata', () => {
        const wrapper = mount(SlicePaginator, {
            props: {
                pagination: { currentPage: 2, perPage: 20, hasNext: true },
            },
            global: { plugins: [PrimeVue] },
        })

        const paginator = wrapper.findComponent(Paginator)
        expect(paginator.props('totalRecords')).toBe(60)
        expect(paginator.props('first')).toBe(20)
    })

    it('forwards PrimeVue page events', async () => {
        const wrapper = mount(SlicePaginator, {
            props: {
                pagination: { currentPage: 2, perPage: 20, hasNext: true },
            },
            global: { plugins: [PrimeVue] },
        })

        wrapper.findComponent(Paginator).vm.$emit('page', {
            page: 2,
            first: 40,
            rows: 20,
        })

        expect(wrapper.emitted('page')).toEqual([
            [{ page: 2, first: 40, rows: 20 }],
        ])
    })

    it('keeps the page-size selector when there is no previous or next page', () => {
        const wrapper = mount(SlicePaginator, {
            props: {
                pagination: { currentPage: 1, perPage: 20, hasNext: false },
            },
            global: { plugins: [PrimeVue] },
        })

        const paginator = wrapper.findComponent(Paginator)
        expect(paginator.exists()).toBe(true)
        expect(paginator.props('totalRecords')).toBe(20)
        expect(paginator.props('rowsPerPageOptions')).toEqual([10, 20, 50])
    })
})
