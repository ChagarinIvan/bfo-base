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
})
