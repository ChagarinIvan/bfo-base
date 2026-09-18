import { describe, expect, it } from 'vitest'
import {
    authenticatedAccountNavigation,
    authenticatedCompetitionNavigation,
    authenticatedPersonsNavigation,
    competitionNavigation,
    personsNavigation,
} from './navigationModels'

describe('hybrid SPA navbar', () => {
    it('keeps migrated competitions and groups in SPA', () => {
        expect(competitionNavigation).toEqual([
            {
                label: 'spa.nav.competitions',
                href: '/app/competitions',
                spa: true,
            },
            {
                label: 'spa.nav.groups',
                href: '/app/groups',
                spa: true,
            },
            { label: 'spa.nav.cups', href: '/app/cups', spa: true },
        ])
        expect(personsNavigation.map((item) => item.href)).toEqual([
            '/app/persons',
            '/app/clubs',
        ])
        expect(personsNavigation[1].spa).toBe(true)
    })

    it('places rank checks in the authenticated persons menu', () => {
        expect(authenticatedCompetitionNavigation).toEqual([])
        expect(authenticatedPersonsNavigation.map((item) => item.href)).toEqual(
            ['/app/rank-checks'],
        )
        expect(authenticatedAccountNavigation.map((item) => item.href)).toEqual(
            ['/app/registration'],
        )
        expect(
            [
                ...competitionNavigation,
                ...personsNavigation,
                ...authenticatedPersonsNavigation,
                ...authenticatedCompetitionNavigation,
                ...authenticatedAccountNavigation,
            ].map((item) => item.href),
        ).not.toEqual(expect.arrayContaining(['/faq', '/faq/api']))
    })
})
