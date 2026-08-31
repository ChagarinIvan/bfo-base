export interface NavigationItem {
    label:
        | 'spa.nav.competitions'
        | 'spa.nav.cups'
        | 'spa.nav.persons'
        | 'spa.nav.clubs'
        | 'spa.nav.ranks'
        | 'spa.nav.groups'
        | 'spa.nav.registration'
    href: string
    spa?: boolean
}

export const competitionNavigation: NavigationItem[] = [
    { label: 'spa.nav.competitions', href: '/app/competitions', spa: true },
    { label: 'spa.nav.cups', href: '/cups' },
]

export const personsNavigation: NavigationItem[] = [
    { label: 'spa.nav.persons', href: '/persons' },
    { label: 'spa.nav.clubs', href: '/app/clubs', spa: true },
    { label: 'spa.nav.ranks', href: '/ranks/list/%D0%9C%D0%A1' },
]

export const authenticatedCompetitionNavigation: NavigationItem[] = [
    { label: 'spa.nav.groups', href: '/groups' },
]

export const authenticatedAccountNavigation: NavigationItem[] = [
    { label: 'spa.nav.registration', href: '/registration' },
]
