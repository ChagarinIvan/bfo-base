import { describe, expect, it } from 'vitest'
import {
    competitionQuery,
    formatDateRange,
    shouldLoadUsers,
} from './competitionModels'

describe('competitions page model', () => {
    it('requests the selected year with the API pagination defaults', () => {
        expect(competitionQuery(2026)).toEqual({
            year: 2026,
            page: 1,
            per_page: 20,
        })
    })

    it('loads authors only for an authenticated empty user cache', () => {
        expect(shouldLoadUsers(true, 0)).toBe(true)
        expect(shouldLoadUsers(false, 0)).toBe(false)
        expect(shouldLoadUsers(true, 1)).toBe(false)
    })

    it('formats the start and end dates in one table column', () => {
        expect(formatDateRange('2026-08-22', '2026-08-23')).toBe(
            '2026-08-22 / 2026-08-23',
        )
    })
})
