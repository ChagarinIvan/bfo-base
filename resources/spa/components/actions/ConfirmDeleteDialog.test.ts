import { describe, expect, it } from 'vitest'
import { t } from '../../i18n'

describe('competition deletion confirmation', () => {
    it('keeps the selected competition name for the confirmation dialog', () => {
        expect(
            t('spa.competition.delete.confirm', { name: 'Minsk Cup' }),
        ).toContain('Minsk Cup')
    })
})
