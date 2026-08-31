import { describe, expect, it } from 'vitest'
import {
    authenticatedAccountNavigation,
    authenticatedCompetitionNavigation,
    competitionNavigation,
    personsNavigation,
} from './navigationModels'

describe('hybrid SPA navbar', () => {
    it('keeps migrated competitions in SPA and public legacy destinations as href links', () => {
        expect(competitionNavigation).toEqual([
            {
                label: 'spa.nav.competitions',
                href: '/app/competitions',
                spa: true,
            },
            { label: 'spa.nav.cups', href: '/cups' },
        ])
        expect(personsNavigation.map((item) => item.href)).toEqual([
            '/persons',
            '/app/clubs',
            '/ranks/list/%D0%9C%D0%A1',
        ])
        expect(personsNavigation[1].spa).toBe(true)
    })

    it('keeps authenticated links outside the removed help menu', () => {
        expect(
            authenticatedCompetitionNavigation.map((item) => item.href),
        ).toEqual(['/groups'])
        expect(authenticatedAccountNavigation.map((item) => item.href)).toEqual(
            ['/registration'],
        )
        expect(
            [
                ...competitionNavigation,
                ...personsNavigation,
                ...authenticatedCompetitionNavigation,
                ...authenticatedAccountNavigation,
            ].map((item) => item.href),
        ).not.toEqual(expect.arrayContaining(['/faq', '/faq/api']))
    })
})
