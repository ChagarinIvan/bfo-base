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

    it('formats short and full audit dates', () => {
        expect(formatImpressionDate(impression.at)).toContain('2026')
        expect(formatImpressionFullDate(impression.at)).toContain('2026')
    })

    it('keeps an invalid date readable', () => {
        expect(formatImpressionDate('unknown')).toBe('unknown')
    })
})
