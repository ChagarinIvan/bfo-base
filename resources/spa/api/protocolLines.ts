import { api } from './client'
import type {
    PaginatedApiResponse,
    Person,
    ProtocolLine,
    ProtocolLineSearchQuery,
} from './types'

export async function extractPerson(protocolLineId: string): Promise<Person> {
    return (
        await api.post<Person>(
            `/protocol-lines/${protocolLineId}/extract-person`,
        )
    ).data
}

export async function setProtocolLinePerson(
    protocolLineId: string,
    personId: string,
): Promise<void> {
    await api.put(`/protocol-lines/${protocolLineId}/person`, { personId })
}

export async function getPersonProtocolLines(
    query: ProtocolLineSearchQuery,
): Promise<PaginatedApiResponse<ProtocolLine>> {
    const response = await api.get<ProtocolLine[]>('/protocol-lines', {
        params: query,
    })

    return {
        data: response.data,
        headers: response.headers as Record<string, unknown>,
    }
}
