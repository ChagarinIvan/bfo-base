import { describe, expect, it } from 'vitest'
import type { Person, ProtocolLine } from '../../api/types'
import { hasPersonMismatch, personProtocolLinesQuery } from './personViewModels'

const person: Person = {
    id: '7',
    lastname: 'Ivanov',
    firstname: 'Ivan',
    birthday: '1990-06-04',
    rankId: 0,
    citizenship: 'belarus',
    clubId: null,
}

const line = (overrides: Partial<ProtocolLine> = {}): ProtocolLine => ({
    id: '11',
    personId: '7',
    firstname: 'Ivan',
    lastname: 'Ivanov',
    distanceId: '12',
    eventId: '13',
    competitionId: '14',
    competitionName: 'Spring Cup',
    eventName: 'Long',
    eventDate: '2026-05-10',
    groupName: 'M21',
    year: '1990',
    time: '01:02:03',
    place: '1',
    completeRank: 'II',
    ...overrides,
})

describe('person view models', () => {
    it('keeps mandatory resources and removes an incomplete name filter', () => {
        expect(
            personProtocolLinesQuery({
                personId: '7',
                withEvent: 1,
                withCompetition: 1,
                competitionName: ' ab ',
                date: '',
            }),
        ).toEqual({
            personId: '7',
            withEvent: 1,
            withCompetition: 1,
        })
    })

    it('includes supported filters and pagination', () => {
        expect(
            personProtocolLinesQuery({
                personId: '7',
                withEvent: 1,
                withCompetition: 1,
                year: 2026,
                competitionName: ' Spring Cup ',
                date: '2026-05-10',
                page: 2,
                perPage: 10,
            }),
        ).toEqual({
            personId: '7',
            withEvent: 1,
            withCompetition: 1,
            year: 2026,
            competitionName: 'Spring Cup',
            date: '2026-05-10',
            page: 2,
            perPage: 10,
        })
    })

    it('marks a line when present person fields differ', () => {
        expect(hasPersonMismatch(line({ firstname: 'John' }), person)).toBe(
            true,
        )
        expect(hasPersonMismatch(line({ year: '1989' }), person)).toBe(true)
        expect(hasPersonMismatch(line(), person)).toBe(false)
    })

    it('does not compare a missing birth year', () => {
        expect(hasPersonMismatch(line({ year: null }), person)).toBe(false)
        expect(
            hasPersonMismatch(line({ year: '1990' }), {
                ...person,
                birthday: null,
            }),
        ).toBe(false)
    })
})
