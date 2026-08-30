import { describe, expect, it } from 'vitest'
import { competitionFormInitialValue } from './competitionFormModels'

describe('competition form', () => {
    it('uses the same complete model for creation and prefilled editing', () => {
        expect(competitionFormInitialValue()).toEqual({
            name: '',
            description: '',
            from: '',
            to: '',
            mass: false,
        })
        expect(
            competitionFormInitialValue({ name: 'Minsk Cup', mass: true }),
        ).toMatchObject({ name: 'Minsk Cup', mass: true })
    })
})
