import type { Impression, User } from '../api/types'

function formatDate(value: string, withSeconds: boolean): string {
    const match = /^(\d{4}-\d{2}-\d{2})(?:T(\d{2}:\d{2}(?::\d{2})?))?/.exec(
        value,
    )

    if (!match) return value

    const time = match[2]
    if (!time) return match[1]

    return `${match[1]} ${withSeconds ? time : time.slice(0, 5)}`
}

export function formatImpressionDate(value: string): string {
    return formatDate(value, false)
}

export function formatImpressionFullDate(value: string): string {
    return formatDate(value, true)
}

export function impressionUserLabel(
    impression: Impression,
    users: User[],
    unknownUser: string,
): string {
    const user = users.find((item) => String(item.id) === impression.by)

    return user?.name || user?.email || unknownUser
}
