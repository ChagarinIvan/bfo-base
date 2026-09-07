import { api } from './client'
import type {
    PersonRankActivationRequest,
    PersonRankHistory,
    ProtocolLine,
} from './types'

export async function getPersonRankHistories(
    personId: string,
): Promise<PersonRankHistory[]> {
    return (
        await api.get<PersonRankHistory[]>(
            `/persons/${personId}/rank-histories`,
        )
    ).data
}

export async function activatePersonRank(
    protocolLineId: string,
    payload: PersonRankActivationRequest,
): Promise<ProtocolLine> {
    return (
        await api.post<ProtocolLine>(
            `/person-rank-history/${protocolLineId}/activation`,
            payload,
        )
    ).data
}

export async function updatePersonRankActivation(
    protocolLineId: string,
    payload: PersonRankActivationRequest,
): Promise<ProtocolLine> {
    return (
        await api.put<ProtocolLine>(
            `/person-rank-history/${protocolLineId}/activation`,
            payload,
        )
    ).data
}
