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
                href: '/app/cups/30',
            },
            {
                eventId: '10',
                cupEventId: '21',
                cupId: '31',
                cupName: 'Велакубак',
                cupType: 'bike',
                href: '/app/cups/31',
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
            '/app/cups/30',
        )
        expect(wrapper.get('.cup-event-badge').classes()).toContain(
            'cup-event-badge',
        )
    })
})
