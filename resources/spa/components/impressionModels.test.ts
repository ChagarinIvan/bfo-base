import { describe, expect, it } from 'vitest'
import {
    formatImpressionDate,
    formatImpressionFullDate,
    impressionUserLabel,
} from './impressionModels'

describe('impression model', () => {
    const impression = {
        at: '2026-08-29T20:41:01+00:00',
        by: '7',
    }

    it('prefers a user name and falls back to email', () => {
        expect(
            impressionUserLabel(
                impression,
                [{ id: 7, name: 'Іван', email: 'ivan@example.com' }],
                'Unknown',
            ),
        ).toBe('Іван')
        expect(
            impressionUserLabel(
                impression,
                [{ id: 7, name: null, email: 'ivan@example.com' }],
                'Unknown',
            ),
        ).toBe('ivan@example.com')
    })

    it('falls back to the supplied unknown-user label', () => {
        expect(impressionUserLabel(impression, [], 'Карыстальнік №7')).toBe(
            'Карыстальнік №7',
        )
    })

    it('formats audit dates as ISO dates without translated month names', () => {
        expect(formatImpressionDate(impression.at)).toBe('2026-08-29 20:41')
        expect(formatImpressionFullDate(impression.at)).toBe(
            '2026-08-29 20:41:01',
        )
    })

    it('keeps an invalid date readable', () => {
        expect(formatImpressionDate('unknown')).toBe('unknown')
    })
})
