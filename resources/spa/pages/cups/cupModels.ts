import type { CupSearchQuery } from '../../api/types'
import {
    NAME_SEARCH_MINIMUM_LENGTH,
    normaliseNameSearch,
} from '../listingModels'

export {
    NAME_SEARCH_DEBOUNCE_MS,
    NAME_SEARCH_MINIMUM_LENGTH,
    debounce,
    hasTooShortNameSearch,
    paginationFromHeaders,
    resetPageOnFilterChange,
} from '../listingModels'

export function cupQuery(filters: CupSearchQuery): CupSearchQuery {
    const query: CupSearchQuery = {}
    const name = normaliseNameSearch(filters.name ?? '')

    if (filters.year !== undefined) query.year = filters.year
    if (name.length >= NAME_SEARCH_MINIMUM_LENGTH) query.name = name
    if (filters.visible !== undefined) query.visible = filters.visible
    if (filters.page !== undefined) query.page = filters.page
    if (filters.perPage !== undefined) query.perPage = filters.perPage

    return query
}
