import type { CupEventSearchQuery } from '../../api/types'
import {
    NAME_SEARCH_MINIMUM_LENGTH,
    normaliseNameSearch,
} from '../listingModels'

export function cupEventQuery(
    filters: CupEventSearchQuery,
): CupEventSearchQuery {
    const query: CupEventSearchQuery = {}
    const name = normaliseNameSearch(filters.name ?? '')

    if (name.length >= NAME_SEARCH_MINIMUM_LENGTH) query.name = name
    if (filters.date?.trim()) query.date = filters.date.trim()
    if (filters.eventIds?.length) query.eventIds = filters.eventIds
    if (filters.page !== undefined) query.page = filters.page
    if (filters.perPage !== undefined) query.perPage = filters.perPage

    return query
}
