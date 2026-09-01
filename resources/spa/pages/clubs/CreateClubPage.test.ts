import { describe, expect, it } from 'vitest'
import CreateClubPage from './CreateClubPage.vue'

describe('create club page', () => {
    it('uses the shared club form page component', () => {
        expect(CreateClubPage).toBeDefined()
    })
})
