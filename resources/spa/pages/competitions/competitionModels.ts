import type {
    ApiErrorItem,
    CompetitionSearchQuery,
    PaginationHeaders,
} from '../../api/types'

export const NAME_SEARCH_MINIMUM_LENGTH = 3
export const NAME_SEARCH_DEBOUNCE_MS = 300

export type Debounced<TArguments extends unknown[]> = ((
    ...arguments_: TArguments
) => void) & {
    cancel: () => void
}

export function normaliseNameSearch(value: string): string {
    return value.trim()
}

export function hasTooShortNameSearch(value: string): boolean {
    const name = normaliseNameSearch(value)

    return name.length > 0 && name.length < NAME_SEARCH_MINIMUM_LENGTH
}

export function competitionQuery(
    filters: CompetitionSearchQuery,
): CompetitionSearchQuery {
    const query: CompetitionSearchQuery = {}
    const name = normaliseNameSearch(filters.name ?? '')
    const date = filters.date?.trim()

    if (filters.year !== undefined) query.year = filters.year
    if (name.length >= NAME_SEARCH_MINIMUM_LENGTH) query.name = name
    if (date) query.date = date
    if (filters.page !== undefined) query.page = filters.page
    if (filters.perPage !== undefined) query.perPage = filters.perPage

    return query
}

export function yearSelectOptions(
    years: number[],
): Array<{ label: string; value: number }> {
    return years.map((year) => ({ label: String(year), value: year }))
}

export function debounce<TArguments extends unknown[]>(
    callback: (...arguments_: TArguments) => void,
    delay = NAME_SEARCH_DEBOUNCE_MS,
): Debounced<TArguments> {
    let timeout: ReturnType<typeof setTimeout> | undefined

    const debounced = (...arguments_: TArguments): void => {
        if (timeout) clearTimeout(timeout)
        timeout = setTimeout(() => callback(...arguments_), delay)
    }

    debounced.cancel = (): void => {
        if (timeout) clearTimeout(timeout)
        timeout = undefined
    }

    return debounced
}

export function resetPageOnFilterChange(currentPage: number): number {
    return currentPage === 1 ? currentPage : 1
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

export function isApiValidationError(exception: unknown): exception is {
    response: { status: 422; data: { errors: ApiErrorItem[] } }
} {
    if (typeof exception !== 'object' || exception === null) return false
    if (!('isAxiosError' in exception) || exception.isAxiosError !== true) {
        return false
    }

    if (!('response' in exception) || typeof exception.response !== 'object') {
        return false
    }

    const response = exception.response as {
        status?: unknown
        data?: { errors?: unknown }
    }

    return response.status === 422 && Array.isArray(response.data?.errors)
}
