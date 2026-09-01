import { describe, expect, it } from 'vitest'
import ClubForm from './ClubForm.vue'

describe('club form', () => {
    it('is a reusable Vue form component', () => {
        expect(ClubForm).toBeDefined()
        expect(ClubForm.__name).toBe('ClubForm')
    })
})
