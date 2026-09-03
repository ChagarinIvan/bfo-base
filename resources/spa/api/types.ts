export interface User {
    id: number
    name: string | null
    email: string
}

export interface Impression {
    at: string
    by: string
}

export interface Competition {
    id: string
    name: string
    description: string
    from: string
    to: string
    year: number
    mass: boolean
    created?: Impression
    updated?: Impression
}

export interface Event {
    id: string
    competitionId: string
    name: string
    description: string
    date: string
    participantsCount: number
    created?: Impression
    updated?: Impression
}

export interface Club {
    id: string
    name: string
    personsCount: number
    created?: Impression
    updated?: Impression
}

export interface ClubOption {
    id: string
    name: string
}

export interface Person {
    id: string
    lastname: string
    firstname: string
    birthday: string | null
    rankId: number
    clubId: string | null
    created?: Impression
    updated?: Impression
}

export interface AuthToken {
    token: string
    token_type: string
}

export interface PaginationHeaders {
    currentPage: number
    perPage: number
    total: number
    lastPage: number
}

export interface PaginatedApiResponse<T> {
    data: T[]
    headers: Record<string, unknown>
}

export interface CreateCompetitionRequest {
    name: string
    description: string
    from: string
    to: string
    mass: boolean
}

export interface ClubSearchQuery {
    name?: string
    page?: number
    perPage?: number
}

export interface PersonSearchQuery {
    name?: string
    clubId?: number
    rankId?: number
    birthYear?: number
    page?: number
    perPage?: number
}

export interface CreateClubRequest {
    name: string
}

export type UpdateClubRequest = CreateClubRequest

export type UpdateCompetitionRequest = CreateCompetitionRequest

export interface CompetitionSearchQuery {
    year?: number
    name?: string
    date?: string
    page?: number
    perPage?: number
}

export interface ApiErrorItem {
    code: string
    field?: string
    message: string
}

export interface ApiErrorResponse {
    errors: ApiErrorItem[]
}
