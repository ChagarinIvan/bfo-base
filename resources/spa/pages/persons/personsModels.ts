import type { PersonSearchQuery } from '../../api/types'
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
    paginationFromHeaders,
    resetPageOnFilterChange,
    shouldLoadUsers,
} from '../listingModels'

export function personQuery(filters: PersonSearchQuery): PersonSearchQuery {
    const query: PersonSearchQuery = {}
    const name = normaliseNameSearch(filters.name ?? '')

    if (name.length >= NAME_SEARCH_MINIMUM_LENGTH) query.name = name
    if (filters.clubId !== undefined) query.clubId = filters.clubId
    if (filters.rankId !== undefined) query.rankId = filters.rankId
    if (filters.birthYear !== undefined) query.birthYear = filters.birthYear
    if (filters.page !== undefined) query.page = filters.page
    if (filters.perPage !== undefined) query.perPage = filters.perPage

    return query
}

export function birthYearOptions(
    currentYear = new Date().getFullYear(),
): number[] {
    return Array.from(
        { length: currentYear - 1920 + 1 },
        (_, index) => currentYear - index,
    )
}
