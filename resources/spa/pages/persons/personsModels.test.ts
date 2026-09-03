import { describe, expect, it } from 'vitest'
import { birthYearOptions, personQuery } from './personsModels'

describe('persons page model', () => {
    it('builds a cumulative query and omits an incomplete name', () => {
        expect(
            personQuery({
                name: '  Ivan  ',
                clubId: 7,
                rankId: 0,
                birthYear: 2001,
                page: 2,
                perPage: 10,
            }),
        ).toEqual({
            name: 'Ivan',
            clubId: 7,
            rankId: 0,
            birthYear: 2001,
            page: 2,
            perPage: 10,
        })
        expect(personQuery({ name: 'ab', rankId: 0 })).toEqual({ rankId: 0 })
    })

    it('generates the inclusive current-year-to-1920 range', () => {
        expect(birthYearOptions(1922)).toEqual([1922, 1921, 1920])
    })
})
