import type { CompetitionSearchQuery } from '../../api/types'
import {
    NAME_SEARCH_MINIMUM_LENGTH,
    normaliseNameSearch,
} from '../listingModels'

export {
    NAME_SEARCH_DEBOUNCE_MS,
    NAME_SEARCH_MINIMUM_LENGTH,
    applyFieldErrors,
    debounce,
    hasTooShortNameSearch,
    isApiValidationError,
    normaliseNameSearch,
    paginationFromHeaders,
    resetPageOnFilterChange,
    shouldLoadUsers,
} from '../listingModels'

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

export function formatDateRange(from: string, to: string): string {
    return `${from} / ${to}`
}

export function massIconClass(mass: boolean): string {
    return mass ? 'pi pi-check-square' : 'pi pi-times-circle'
}

export function isDateRangeValid(from: string, to: string): boolean {
    return !from || !to || to >= from
}
