import { describe, expect, it } from 'vitest'
import { competitionActionRoute } from '../../components/actions/actionModels'

describe('competition edit page', () => {
    it('uses the authenticated edit route for a prefilled competition', () => {
        expect(competitionActionRoute('42')).toBe('/app/competitions/42/edit')
    })
})
