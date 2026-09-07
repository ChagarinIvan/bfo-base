// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import ClubSelect from './ClubSelect.vue'

describe('club select', () => {
    it('uses supplied cached club options and can prepend the all option', () => {
        const wrapper = mount(ClubSelect, {
            props: {
                modelValue: '',
                clubs: [{ id: '7', name: 'Клуб' }],
                inputId: 'club',
                label: 'Клуб',
                includeAll: true,
            },
            global: {
                stubs: {
                    Select: {
                        props: ['options'],
                        template:
                            '<output>{{ JSON.stringify(options) }}</output>',
                    },
                },
            },
        })

        expect(wrapper.get('label').attributes('for')).toBe('club')
        expect(wrapper.get('output').text()).toBe(
            '[{"id":"","name":"Усе"},{"id":"7","name":"Клуб"}]',
        )
    })
})
