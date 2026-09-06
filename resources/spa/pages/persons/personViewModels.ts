import type { ProtocolLineSearchQuery } from '../../api/types'
import {
    NAME_SEARCH_MINIMUM_LENGTH,
    normaliseNameSearch,
} from '../listingModels'

export {
    applyFieldErrors,
    debounce,
    hasTooShortNameSearch,
    isApiValidationError,
    paginationFromHeaders,
    resetPageOnFilterChange,
} from '../listingModels'

export function personProtocolLinesQuery(
    filters: ProtocolLineSearchQuery,
): ProtocolLineSearchQuery {
    const query: ProtocolLineSearchQuery = {
        personId: filters.personId,
        withEvent: 1,
        withCompetition: 1,
    }
    const competitionName = normaliseNameSearch(filters.competitionName ?? '')

    if (filters.year !== undefined) query.year = filters.year
    if (competitionName.length >= NAME_SEARCH_MINIMUM_LENGTH) {
        query.competitionName = competitionName
    }
    if (filters.date) query.date = filters.date
    if (filters.page !== undefined) query.page = filters.page
    if (filters.perPage !== undefined) query.perPage = filters.perPage

    return query
}
