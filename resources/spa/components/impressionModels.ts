import type { Impression, User } from '../api/types'

const shortDateFormatter = new Intl.DateTimeFormat('be-BY', {
    dateStyle: 'medium',
    timeStyle: 'short',
})

const fullDateFormatter = new Intl.DateTimeFormat('be-BY', {
    dateStyle: 'full',
    timeStyle: 'long',
})

function formatDate(value: string, formatter: Intl.DateTimeFormat): string {
    const date = new Date(value)

    return Number.isNaN(date.getTime()) ? value : formatter.format(date)
}

export function formatImpressionDate(value: string): string {
    return formatDate(value, shortDateFormatter)
}

export function formatImpressionFullDate(value: string): string {
    return formatDate(value, fullDateFormatter)
}

export function impressionUserLabel(
    impression: Impression,
    users: User[],
    unknownUser: string,
): string {
    const user = users.find((item) => String(item.id) === impression.by)

    return user?.name || user?.email || unknownUser
}
