import { api } from './client'
import type {
    PaginatedApiResponse,
    ProtocolLine,
    ProtocolLineSearchQuery,
} from './types'

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
