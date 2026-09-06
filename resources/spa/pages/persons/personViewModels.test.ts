import { describe, expect, it } from 'vitest'
import { personProtocolLinesQuery } from './personViewModels'

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
})
