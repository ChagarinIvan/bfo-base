import { describe, expect, it } from 'vitest'
import {
    protocolLineEventUrl,
    sanitizeVisibleColumns,
    tableStorageKey,
} from './tableModels'

describe('table models', () => {
    it('keeps only available columns and restores defaults when none remain', () => {
        expect(
            sanitizeVisibleColumns(
                ['name', 'unknown', 'created'],
                ['name', 'date'],
                ['name', 'date'],
            ),
        ).toEqual(['name'])
        expect(
            sanitizeVisibleColumns([], ['name', 'date'], ['name', 'date']),
        ).toEqual(['name', 'date'])
    })

    it('separates preferences by listing and access variant', () => {
        expect(tableStorageKey('persons', false)).not.toBe(
            tableStorageKey('persons', true),
        )
        expect(tableStorageKey('persons', false)).not.toBe(
            tableStorageKey('competitions', false),
        )
    })

    it('builds a canonical protocol-line event anchor', () => {
        expect(protocolLineEventUrl('12', '901', '77')).toBe(
            '/app/events/12?distanceId=77#protocol-line-901',
        )
        expect(protocolLineEventUrl('12', '901')).toBe(
            '/app/events/12#protocol-line-901',
        )
    })
})
