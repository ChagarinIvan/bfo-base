// @vitest-environment happy-dom

import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import type { EventProcessingStatus } from '../api/types'
import EventProcessingStatusBadge from './EventProcessingStatusBadge.vue'

describe('event processing status badge', () => {
    it('shows every status in English with its own colour class', () => {
        const statuses: Array<[EventProcessingStatus, string]> = [
            ['parsing', 'Parsing'],
            ['identifying', 'Identifying'],
            ['rebuildingRanks', 'Rebuilding Ranks'],
            ['ready', 'Ready'],
            ['parsingError', 'Parsing Error'],
            ['identifyingError', 'Identification Error'],
            ['rebuildingRanksError', 'Rank Rebuild Error'],
        ]

        const classes = statuses.map(([status, label]) => {
            const badge = mount(EventProcessingStatusBadge, {
                props: { status },
            }).get('.event-processing-badge')

            expect(badge.text()).toBe(label)

            return badge
                .classes()
                .find((name) => name.startsWith('event-processing-badge--'))
        })

        expect(new Set(classes).size).toBe(statuses.length)
    })
})
