// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import PrimeVue from 'primevue/config'
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

    it('shows a delete confirmation before following the legacy delete route', async () => {
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
                plugins: [PrimeVue],
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
        ).toBe(false)

        await wrapper.findAll('.action-menu .p-button')[1].trigger('click')

        expect(document.body.textContent).toContain(
            'Сапраўды выдаліць «Іваноў Ян»?',
        )
        expect(document.body.querySelector('.p-dialog')).not.toBeNull()
    })
})
