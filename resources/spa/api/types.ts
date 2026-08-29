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

export interface ApiResponse<T> {
    data: T
}

export interface AuthToken {
    token: string
    token_type: string
}

export interface CreateCompetitionRequest {
    name: string
    description: string
    from: string
    to: string
    mass: boolean
}

export interface ApiErrorItem {
    code: string
    field?: string
    message: string
}

export interface ApiErrorResponse {
    errors: ApiErrorItem[]
}
