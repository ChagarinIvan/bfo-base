import { api } from './client'
import type {
    Group,
    GroupSearchQuery,
    PaginatedApiResponse,
    UpdateGroupRequest,
} from './types'

export async function getGroups(
    query: GroupSearchQuery = {},
): Promise<PaginatedApiResponse<Group>> {
    const response = await api.get<Group[]>('/groups', { params: query })
    return {
        data: response.data,
        headers: response.headers as Record<string, unknown>,
    }
}

export async function getGroup(id: string): Promise<Group> {
    return (await api.get<Group>(`/groups/${id}`)).data
}

export async function updateGroup(
    id: string,
    payload: UpdateGroupRequest,
): Promise<Group> {
    return (await api.put<Group>(`/groups/${id}`, payload)).data
}

export async function deleteGroup(id: string): Promise<void> {
    await api.delete(`/groups/${id}`)
}

export async function mergeGroups(
    sourceId: string,
    targetGroupId: string,
): Promise<void> {
    await api.post(`/groups/${sourceId}/merge`, { targetGroupId })
}
