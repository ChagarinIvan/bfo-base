import { describe, expect, it } from 'vitest'
import { cupEventQuery } from './cupViewModels'

describe('cup event query', () => {
    it('keeps valid filters and pagination', () => {
        expect(
            cupEventQuery({
                name: '  Spring  ',
                date: '2026-05-10',
                page: 2,
                perPage: 50,
            }),
        ).toEqual({
            name: 'Spring',
            date: '2026-05-10',
            page: 2,
            perPage: 50,
        })
    })

    it('keeps event IDs for programmatic stage filtering', () => {
        expect(cupEventQuery({ eventIds: ['10', '20'] })).toEqual({
            eventIds: ['10', '20'],
        })
    })

    it('drops an incomplete name filter', () => {
        expect(cupEventQuery({ name: 'ab', page: 1 })).toEqual({ page: 1 })
    })
})
