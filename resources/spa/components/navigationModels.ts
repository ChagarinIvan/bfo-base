export interface NavigationItem {
    label:
        | 'spa.nav.competitions'
        | 'spa.nav.cups'
        | 'spa.nav.persons'
        | 'spa.nav.clubs'
        | 'spa.nav.groups'
        | 'spa.nav.registration'
        | 'spa.nav.rank_checks'
    href: string
    spa?: boolean
}

export const competitionNavigation: NavigationItem[] = [
    { label: 'spa.nav.competitions', href: '/app/competitions', spa: true },
    { label: 'spa.nav.groups', href: '/app/groups', spa: true },
    { label: 'spa.nav.cups', href: '/app/cups', spa: true },
]

export const personsNavigation: NavigationItem[] = [
    { label: 'spa.nav.persons', href: '/app/persons', spa: true },
    { label: 'spa.nav.clubs', href: '/app/clubs', spa: true },
]

export const authenticatedPersonsNavigation: NavigationItem[] = [
    { label: 'spa.nav.rank_checks', href: '/app/rank-checks', spa: true },
]

export const authenticatedCompetitionNavigation: NavigationItem[] = []

export const authenticatedAccountNavigation: NavigationItem[] = [
    { label: 'spa.nav.registration', href: '/app/registration', spa: true },
]
