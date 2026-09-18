import { describe, expect, it } from 'vitest'
import EditCupPage from './EditCupPage.vue'

describe('cup edit page', () => {
    it('is implemented as an SPA page', () => {
        expect(EditCupPage).toBeDefined()
        expect(EditCupPage.__name).toBe('EditCupPage')
    })
})
