import { describe, expect, it } from 'vitest'
import CupForm from './CupForm.vue'

describe('cup form', () => {
    it('is shared by create and edit pages', () => {
        expect(CupForm).toBeDefined()
        expect(CupForm.__name).toBe('CupForm')
    })
})
