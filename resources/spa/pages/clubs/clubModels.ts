import type { ClubSearchQuery } from '../../api/types'
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

export function clubQuery(filters: ClubSearchQuery): ClubSearchQuery {
    const query: ClubSearchQuery = {}
    const name = normaliseNameSearch(filters.name ?? '')

    if (name.length >= NAME_SEARCH_MINIMUM_LENGTH) query.name = name
    if (filters.page !== undefined) query.page = filters.page
    if (filters.perPage !== undefined) query.perPage = filters.perPage

    return query
}
