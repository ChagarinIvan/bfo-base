// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import CupEventBadges from './CupEventBadges.vue'

describe('cup event badges', () => {
    it('renders linked, coloured cup-group badges', () => {
        const contexts = [
            {
                eventId: '10',
                cupEventId: '20',
                cupId: '30',
                cupName: 'Кубак спрынту',
                cupType: 'sprint',
                groupId: 'M_0_',
                groupName: 'М',
                href: '/cups/30/20/M_0_/show',
            },
            {
                eventId: '10',
                cupEventId: '21',
                cupId: '31',
                cupName: 'Велакубак',
                cupType: 'bike',
                groupId: 'W_0_',
                groupName: 'Ж',
                href: '/cups/31/21/W_0_/show',
            },
        ]
        const wrapper = mount(CupEventBadges, {
            props: { contexts },
            global: {
                stubs: { Popover: { template: '<slot />' } },
            },
        })

        expect(wrapper.findAll('.cup-event-badge')).toHaveLength(2)
        expect(wrapper.get('.cup-event-badge').attributes('href')).toBe(
            '/cups/30/20/M_0_/show',
        )
        expect(wrapper.get('.cup-event-badge').text()).toContain('М')
        expect(wrapper.get('.cup-event-badge').classes()).toContain(
            'cup-group-badge--green',
        )
    })
})
