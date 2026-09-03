// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import PersonTable from './PersonTable.vue'

describe('person table', () => {
    it('keeps legacy detail links and resolves club names from options', () => {
        const wrapper = mount(PersonTable, {
            props: {
                persons: [
                    {
                        id: '7',
                        lastname: 'Іваноў',
                        firstname: 'Ян',
                        birthday: '2001-06-04',
                        rankId: 6,
                        clubId: '17',
                    },
                ],
                users: [],
                clubs: [{ id: '17', name: 'Клуб' }],
                rankLabels: { 6: 'I' },
            },
            global: {
                stubs: {
                    RouterLink: { template: '<a><slot /></a>' },
                    ImpressionDetails: true,
                },
            },
        })

        expect(wrapper.findAll('a[href="/persons/7/show"]')).toHaveLength(2)
        expect(wrapper.text()).toContain('Клуб')
        expect(wrapper.text()).toContain('2001')
        expect(wrapper.text()).toContain('I')
    })

    it('renders edit and delete icon buttons in one authenticated actions column', () => {
        const wrapper = mount(PersonTable, {
            props: {
                persons: [
                    {
                        id: '7',
                        lastname: 'Іваноў',
                        firstname: 'Ян',
                        birthday: null,
                        rankId: 6,
                        clubId: null,
                    },
                ],
                users: [],
                authenticated: true,
            },
            global: {
                stubs: {
                    RouterLink: { template: '<a><slot /></a>' },
                    ImpressionDetails: true,
                },
            },
        })

        expect(wrapper.text()).toContain('Дзеянні')
        expect(wrapper.findAll('.action-menu .p-button')).toHaveLength(2)
        expect(wrapper.find('.action-menu .pi-pencil').exists()).toBe(true)
        expect(wrapper.find('.action-menu .pi-trash').exists()).toBe(true)
        expect(
            wrapper.find('.action-menu a[href="/persons/7/edit"]').exists(),
        ).toBe(true)
        expect(
            wrapper.find('.action-menu a[href="/persons/7/delete"]').exists(),
        ).toBe(true)
    })
})
