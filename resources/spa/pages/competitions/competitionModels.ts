import type {
    ApiErrorItem,
    CompetitionQuery,
    PaginationHeaders,
} from '../../api/types'

export function competitionQuery(
    year: number,
    page = 1,
    perPage = 20,
): CompetitionQuery {
    return { year, page, per_page: perPage }
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

export function paginationFromHeaders(
    headers: Record<string, unknown>,
): PaginationHeaders {
    const read = (name: string, fallback: number): number => {
        const value = headers[name] ?? headers[name.toLowerCase()]
        const parsed = Number(value)
        return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback
    }

    return {
        currentPage: read('x-pagination-current-page', 1),
        perPage: read('x-pagination-per-page', 20),
        total: read('x-pagination-total', 0),
        lastPage: read('x-pagination-last-page', 1),
    }
}

export function massIconClass(mass: boolean): string {
    return mass ? 'pi pi-check-square' : 'pi pi-times-circle'
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
