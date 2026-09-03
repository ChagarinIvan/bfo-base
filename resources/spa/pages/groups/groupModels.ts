import type { GroupSearchQuery } from '../../api/types'
import { normaliseNameSearch } from '../listingModels'

export {
    debounce,
    paginationFromHeaders,
    resetPageOnFilterChange,
} from '../listingModels'
export const GROUP_NAME_SEARCH_MINIMUM_LENGTH = 1
export const GROUP_NAME_SEARCH_DEBOUNCE_MS = 300

export function groupQuery(filters: GroupSearchQuery): GroupSearchQuery {
    const query: GroupSearchQuery = {}
    const name = normaliseNameSearch(filters.name ?? '')
    if (name.length >= GROUP_NAME_SEARCH_MINIMUM_LENGTH) query.name = name
    if (filters.excludeId) query.excludeId = filters.excludeId
    if (filters.page) query.page = filters.page
    if (filters.perPage) query.perPage = filters.perPage
    return query
}
