import { describe, expect, it } from 'vitest'
import {
    CUP_TYPES,
    cupTypeDefinition,
    cupTypeDefinitions,
} from './cupTypeModels'

describe('cup type models', () => {
    it('defines an icon for every backend cup type', () => {
        expect(CUP_TYPES).toHaveLength(10)
        CUP_TYPES.forEach((type) => {
            expect(cupTypeDefinitions[type]).toMatchObject({
                type,
                icon: expect.stringContaining('fa-'),
                fallback: false,
            })
        })
    })

    it('returns a neutral definition for an unknown type', () => {
        expect(cupTypeDefinition('future_type')).toMatchObject({
            type: 'unknown',
            icon: 'fas fa-question-circle',
            fallback: true,
        })
    })
})
