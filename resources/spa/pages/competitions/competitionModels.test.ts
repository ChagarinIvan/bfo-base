import { afterEach, describe, expect, it, vi } from 'vitest'
import {
    applyFieldErrors,
    competitionQuery,
    debounce,
    formatDateRange,
    hasTooShortNameSearch,
    isApiValidationError,
    massIconClass,
    paginationFromHeaders,
    resetPageOnFilterChange,
    shouldLoadUsers,
    yearSelectOptions,
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

    it('provides string labels for year select filtering', () => {
        expect(yearSelectOptions([2026, 2025])).toEqual([
            { label: '2026', value: 2026 },
            { label: '2025', value: 2025 },
        ])
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

    it('recognises an API validation response', () => {
        expect(
            isApiValidationError({
                isAxiosError: true,
                response: {
                    status: 422,
                    data: {
                        errors: [
                            {
                                code: 'validation_error',
                                field: 'date',
                                message: 'validation.date_format',
                            },
                        ],
                    },
                },
            }),
        ).toBe(true)

        expect(
            isApiValidationError({
                isAxiosError: true,
                response: { status: 500, data: { errors: [] } },
            }),
        ).toBe(false)
    })
})
