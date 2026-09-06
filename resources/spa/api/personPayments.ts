import { api } from './client'
import type {
    PaginatedApiResponse,
    PersonPayment,
    PersonPaymentRequest,
    PersonPaymentSearchQuery,
} from './types'

export async function getPersonPayments(
    query: PersonPaymentSearchQuery,
): Promise<PaginatedApiResponse<PersonPayment>> {
    const response = await api.get<PersonPayment[]>('/persons/payments', {
        params: query,
    })

    return {
        data: response.data,
        headers: response.headers as Record<string, unknown>,
    }
}

export async function createOrUpdatePersonPayment(
    personId: string,
    payload: PersonPaymentRequest,
): Promise<PersonPayment> {
    return (
        await api.post<PersonPayment>('/persons/payments', {
            personId,
            ...payload,
        })
    ).data
}
