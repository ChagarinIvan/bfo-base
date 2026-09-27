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
                fallback: false,
            })
            expect(
                cupTypeDefinitions[type].icon ||
                    cupTypeDefinitions[type].illustration,
            ).toBeTruthy()
        })
    })

    it('uses distinct visual identities for age and competition categories', () => {
        expect(cupTypeDefinitions.elite.icon).not.toBe(
            cupTypeDefinitions.master.icon,
        )
        expect(cupTypeDefinitions.master.icon).toBe(
            cupTypeDefinitions.new_master.icon,
        )
        expect(cupTypeDefinitions.juniors.icon).not.toBe(
            cupTypeDefinitions.youth.icon,
        )
        expect(cupTypeDefinitions.elk_path.illustration).toBe('moose')
    })

    it('returns a neutral definition for an unknown type', () => {
        expect(cupTypeDefinition('future_type')).toMatchObject({
            type: 'unknown',
            icon: 'fas fa-question-circle',
            fallback: true,
        })
    })
})
