import type { GroupEventsQuery, GroupSearchQuery } from '../../api/types'
import {
    NAME_SEARCH_MINIMUM_LENGTH,
    normaliseNameSearch,
} from '../listingModels'

export {
    debounce,
    hasTooShortNameSearch,
    paginationFromHeaders,
    resetPageOnFilterChange,
} from '../listingModels'
export const GROUP_NAME_SEARCH_MINIMUM_LENGTH = 1

export function groupQuery(filters: GroupSearchQuery): GroupSearchQuery {
    const query: GroupSearchQuery = {}
    const name = normaliseNameSearch(filters.name ?? '')
    if (name.length >= GROUP_NAME_SEARCH_MINIMUM_LENGTH) query.name = name
    if (filters.excludeId) query.excludeId = filters.excludeId
    if (filters.page) query.page = filters.page
    if (filters.perPage) query.perPage = filters.perPage
    return query
}

export function groupEventsQuery(filters: GroupEventsQuery): GroupEventsQuery {
    const query: GroupEventsQuery = {
        groupId: filters.groupId,
        withCompetition: 1,
    }
    const competitionName = normaliseNameSearch(filters.competitionName ?? '')

    if (competitionName.length >= NAME_SEARCH_MINIMUM_LENGTH) {
        query.competitionName = competitionName
    }
    if (filters.year !== undefined) query.year = filters.year
    if (filters.date) query.date = filters.date
    if (filters.page !== undefined) query.page = filters.page
    if (filters.perPage !== undefined) query.perPage = filters.perPage

    return query
}
