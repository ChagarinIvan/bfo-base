import { describe, expect, it } from 'vitest'
import { clubQuery, hasTooShortNameSearch } from './clubModels'

describe('clubs page model', () => {
    it('normalises a valid name filter and keeps pagination parameters', () => {
        expect(clubQuery({ name: '  Minsk  ', page: 2, perPage: 10 })).toEqual({
            name: 'Minsk',
            page: 2,
            perPage: 10,
        })
    })

    it('does not send an empty or too-short name filter', () => {
        expect(clubQuery({ name: ' ab ' })).toEqual({})
        expect(hasTooShortNameSearch('ab')).toBe(true)
    })
})
