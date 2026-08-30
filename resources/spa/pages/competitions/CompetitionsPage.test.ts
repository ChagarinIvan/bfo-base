import { describe, expect, it } from 'vitest'
import {
    competitionQuery,
    formatDateRange,
    massIconClass,
    paginationFromHeaders,
    shouldLoadUsers,
} from './competitionModels'

describe('competitions page model', () => {
    it('requests the selected year with the API pagination defaults', () => {
        expect(competitionQuery(2026, 2, 10)).toEqual({
            year: 2026,
            page: 2,
            per_page: 10,
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

    it('reads pagination metadata from API response headers', () => {
        expect(
            paginationFromHeaders({
                'x-pagination-current-page': '2',
                'x-pagination-per-page': '10',
                'x-pagination-total': '31',
                'x-pagination-last-page': '4',
            }),
        ).toEqual({
            currentPage: 2,
            perPage: 10,
            total: 31,
            lastPage: 4,
        })
    })

    it('uses a square check for mass starts and a red-accented false icon', () => {
        expect(massIconClass(true)).toBe('pi pi-check-square')
        expect(massIconClass(false)).toBe('pi pi-times-circle')
    })
})
