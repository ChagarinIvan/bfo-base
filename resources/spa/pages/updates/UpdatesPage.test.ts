// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import UpdatesPage from './UpdatesPage.vue'
import { updates } from './updates'

describe('updates page', () => {
    it('shows short chronological rows with expandable details', () => {
        const wrapper = mount(UpdatesPage, {
            global: {
                stubs: {
                    RouterLink: {
                        props: ['to'],
                        template: '<a :href="to"><slot /></a>',
                    },
                },
            },
        })

        const rows = wrapper.findAll('.update-entry')
        expect(rows).toHaveLength(updates.length)
        expect(rows[0]?.text()).toContain(updates[0]?.title)
        expect(rows[0]?.find('details').exists()).toBe(true)
        expect(rows[0]?.find('summary').exists()).toBe(true)
        expect(wrapper.text()).toContain('не дата публікацыі')
    })

    it('renders current destinations and checked replacement tasks', () => {
        const wrapper = mount(UpdatesPage, {
            global: {
                stubs: {
                    RouterLink: {
                        props: ['to'],
                        template: '<a :href="to"><slot /></a>',
                    },
                },
            },
        })

        expect(wrapper.find('a[href="/app/cups"]').exists()).toBe(true)
        expect(
            wrapper
                .find('a[href*="specs/039-retire-legacy-web/tasks.md#L26"]')
                .exists(),
        ).toBe(true)
        expect(wrapper.find('.update-entry__example').exists()).toBe(true)
    })
})
