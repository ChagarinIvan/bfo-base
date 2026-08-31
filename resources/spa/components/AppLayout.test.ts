import { describe, expect, it } from 'vitest'
import {
    authenticatedAccountNavigation,
    authenticatedCompetitionNavigation,
    authenticatedHelpNavigation,
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
            '/clubs',
            '/ranks/list/%D0%9C%D0%A1',
        ])
    })

    it('contains private legacy links only in the authenticated groups', () => {
        expect(
            authenticatedCompetitionNavigation.map((item) => item.href),
        ).toEqual(['/groups'])
        expect(authenticatedHelpNavigation.map((item) => item.href)).toEqual([
            '/faq',
            '/faq/api',
        ])
        expect(authenticatedAccountNavigation.map((item) => item.href)).toEqual(
            ['/registration'],
        )
    })
})
