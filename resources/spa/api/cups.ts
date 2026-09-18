import { api } from './client'
import type {
    Cup,
    CupSearchQuery,
    CreateCupRequest,
    UpdateCupRequest,
} from './types'

export async function getCups(query: CupSearchQuery) {
    return api.get<Cup[]>('/cups', { params: query })
}

export async function getCup(id: string) {
    const response = await api.get<Cup>(`/cups/${id}`)
    return response.data
}

export async function createCup(value: CreateCupRequest) {
    const response = await api.post<Cup>('/cups', value)
    return response.data
}

export async function updateCup(id: string, value: UpdateCupRequest) {
    const response = await api.put<Cup>(`/cups/${id}`, value)
    return response.data
}
