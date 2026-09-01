import { describe, expect, it, vi } from 'vitest'
import {
    applyFieldErrors,
    debounce,
    hasTooShortNameSearch,
    paginationFromHeaders,
    resetPageOnFilterChange,
} from './listingModels'

describe('listing models', () => {
    it('trims names and detects short non-empty searches', () => {
        expect(hasTooShortNameSearch('  ab ')).toBe(true)
        expect(hasTooShortNameSearch('  abc ')).toBe(false)
        expect(hasTooShortNameSearch('  ')).toBe(false)
    })

    it('debounces a callback and keeps only the latest arguments', () => {
        vi.useFakeTimers()
        const callback = vi.fn()
        const debounced = debounce(callback)

        debounced('first')
        debounced('second')
        vi.advanceTimersByTime(300)

        expect(callback).toHaveBeenCalledOnce()
        expect(callback).toHaveBeenCalledWith('second')
        vi.useRealTimers()
    })

    it('resets pagination after a filter change', () => {
        expect(resetPageOnFilterChange(4)).toBe(1)
        expect(resetPageOnFilterChange(1)).toBe(1)
    })

    it('reads pagination headers', () => {
        expect(
            paginationFromHeaders({
                'x-pagination-current-page': '2',
                'x-pagination-per-page': '10',
                'x-pagination-total': '31',
                'x-pagination-last-page': '4',
            }),
        ).toEqual({ currentPage: 2, perPage: 10, total: 31, lastPage: 4 })
    })

    it('maps field errors', () => {
        const fieldErrors: Record<string, string> = {}

        applyFieldErrors(
            [{ code: 'validation_error', field: 'name', message: 'Required' }],
            fieldErrors,
        )

        expect(fieldErrors).toEqual({ name: 'Required' })
    })
})
