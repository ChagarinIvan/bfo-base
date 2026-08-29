import { describe, expect, it } from 'vitest'
import { applyFieldErrors, isDateRangeValid } from './competitionModels'

describe('create competition page model', () => {
    it('accepts same-day and later end dates', () => {
        expect(isDateRangeValid('2026-01-01', '2026-01-01')).toBe(true)
        expect(isDateRangeValid('2026-01-01', '2026-01-02')).toBe(true)
    })

    it('rejects an end date before the start date', () => {
        expect(isDateRangeValid('2026-01-02', '2026-01-01')).toBe(false)
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
