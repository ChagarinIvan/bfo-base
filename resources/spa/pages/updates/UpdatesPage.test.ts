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

    it('renders only useful site destinations without arrow icons', () => {
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
        expect(wrapper.find('a[href^="https://github.com/"]').exists()).toBe(
            false,
        )
        expect(wrapper.find('.update-entry__link .pi').exists()).toBe(false)
        const parserEntry = wrapper
            .findAll('.update-entry')
            .find((entry) =>
                entry.text().includes('Правільныя групы з OBelarus.net'),
            )
        expect(parserEntry?.find('.update-entry__example').exists()).toBe(false)
        expect(parserEntry?.find('.update-entry__link').exists()).toBe(false)
        expect(wrapper.find('.update-entry__example').exists()).toBe(true)
    })
})
