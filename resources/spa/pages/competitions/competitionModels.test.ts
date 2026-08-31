import { afterEach, describe, expect, it, vi } from 'vitest'
import {
    applyFieldErrors,
    competitionQuery,
    debounce,
    formatDateRange,
    hasTooShortNameSearch,
    massIconClass,
    paginationFromHeaders,
    resetPageOnFilterChange,
    shouldLoadUsers,
} from './competitionModels'

describe('competition models', () => {
    afterEach(() => {
        vi.useRealTimers()
    })

    it('builds camelCase query parameters and omits a short name', () => {
        expect(
            competitionQuery({
                year: 2026,
                name: '  Minsk  ',
                date: '2026-08-22',
                page: 2,
                perPage: 10,
            }),
        ).toEqual({
            year: 2026,
            name: 'Minsk',
            date: '2026-08-22',
            page: 2,
            perPage: 10,
        })

        expect(competitionQuery({ name: ' ab ' })).toEqual({})
    })

    it('reports only non-empty searches shorter than three characters', () => {
        expect(hasTooShortNameSearch('')).toBe(false)
        expect(hasTooShortNameSearch('  ')).toBe(false)
        expect(hasTooShortNameSearch(' ab ')).toBe(true)
        expect(hasTooShortNameSearch(' abc ')).toBe(false)
    })

    it('debounces repeated filter changes', () => {
        vi.useFakeTimers()
        const callback = vi.fn()
        const debounced = debounce(callback)

        debounced('first')
        debounced('second')
        vi.advanceTimersByTime(299)
        expect(callback).not.toHaveBeenCalled()

        vi.advanceTimersByTime(1)
        expect(callback).toHaveBeenCalledOnce()
        expect(callback).toHaveBeenCalledWith('second')
    })

    it('resets pagination to the first page after filters change', () => {
        expect(resetPageOnFilterChange(4)).toBe(1)
        expect(resetPageOnFilterChange(1)).toBe(1)
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

    it('maps API validation errors to form fields', () => {
        const fieldErrors: Record<string, string> = {}

        applyFieldErrors(
            [{ code: 'validation_error', field: 'name', message: 'Required' }],
            fieldErrors,
        )

        expect(fieldErrors).toEqual({ name: 'Required' })
    })
})
