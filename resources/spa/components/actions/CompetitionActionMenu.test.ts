import { describe, expect, it } from 'vitest'
import { competitionActionRoute } from './actionModels'

describe('competition action menu', () => {
    it('builds the authenticated edit destination for a competition', () => {
        expect(competitionActionRoute('42')).toBe('/app/competitions/42/edit')
    })

    it('does not coerce the API competition identifier', () => {
        expect(competitionActionRoute('001')).toBe('/app/competitions/001/edit')
    })
})
