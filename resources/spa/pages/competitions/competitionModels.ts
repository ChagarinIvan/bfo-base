import type { ApiErrorItem, CompetitionQuery } from '../../api/types'

export function competitionQuery(year: number): CompetitionQuery {
    return { year, page: 1, per_page: 20 }
}

export function formatDateRange(from: string, to: string): string {
    return `${from} / ${to}`
}

export function shouldLoadUsers(
    authenticated: boolean,
    usersCount: number,
): boolean {
    return authenticated && usersCount === 0
}

export function isDateRangeValid(from: string, to: string): boolean {
    return !from || !to || to >= from
}

export function applyFieldErrors(
    errors: ApiErrorItem[],
    fieldErrors: Record<string, string>,
): void {
    for (const error of errors) {
        if (error.field) fieldErrors[error.field] = error.message
    }
}
