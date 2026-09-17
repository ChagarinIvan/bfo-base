// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
import Select from 'primevue/select'
import { describe, expect, it } from 'vitest'
import SlicePaginator from './SlicePaginator.vue'

describe('slice paginator', () => {
    it('emits the next page only when the probe row exists', async () => {
        const wrapper = mount(SlicePaginator, {
            props: {
                pagination: { currentPage: 2, perPage: 20, hasNext: true },
            },
            global: { plugins: [PrimeVue] },
        })

        const buttons = wrapper.findAll('button')
        expect(buttons[0].attributes('disabled')).toBeUndefined()
        await buttons[1].trigger('click')

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

        expect(wrapper.find('nav').exists()).toBe(true)
        const buttons = wrapper.findAll('button')
        expect(buttons).toHaveLength(2)
        expect(buttons[0].attributes('disabled')).toBeDefined()
        expect(buttons[1].attributes('disabled')).toBeDefined()
        expect(wrapper.findComponent(Select).exists()).toBe(true)
    })
})
