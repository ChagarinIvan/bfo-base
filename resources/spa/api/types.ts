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
    competitionName?: string | null
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

export interface Group {
    id: string
    name: string
    distancesCount: number
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
    citizenship: string
    clubId: string | null
    created?: Impression
    updated?: Impression
}

export interface PersonFormRequest {
    lastname: string
    firstname: string
    birthday: string | null
    clubId: string | null
    citizenship: string
}

export interface PersonPrompt {
    id: string
    personId: string
    prompt: string
    metaphone: string
    created?: Impression
    updated?: Impression
}

export interface PersonPayment {
    id: string
    personId: string
    year: string
    date: string
    created?: Impression
    updated?: Impression
}

export interface PersonPaymentSearchQuery {
    personId: string
    year?: number
    page?: number
    perPage?: number
}

export interface PersonPaymentRequest {
    date: string
}

export interface PersonRankHistory {
    id: string
    personId: string
    protocolLineId: string
    distanceId: string
    eventId: string
    competitionId: string
    rankId: number
    changeType: string
    achievedOn: string
    activatedOn: string | null
    startedOn: string
    finishedOn: string | null
}

export interface PersonRankActivationRequest {
    date: string | null
}

export interface ProtocolLine {
    id: string
    personId: string
    firstname: string
    lastname: string
    distanceId: string
    eventId: string | null
    competitionId: string | null
    competitionName: string | null
    eventName: string | null
    eventDate: string | null
    groupName: string | null
    year: string | null
    time: string | null
    place: string | null
    completeRank: string | null
}

export interface ProtocolLineSearchQuery {
    personId: string
    withEvent: 1
    withCompetition: 1
    year?: number
    competitionName?: string
    date?: string
    page?: number
    perPage?: number
}

export interface PersonPromptSearchQuery {
    personId: string
    page?: number
    perPage?: number
}

export interface PersonPromptRequest {
    prompt: string
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

export interface GroupSearchQuery {
    name?: string
    excludeId?: string
    page?: number
    perPage?: number
}

export interface GroupEventsQuery {
    groupId: string
    withCompetition: 1
    competitionName?: string
    year?: number
    date?: string
    page?: number
    perPage?: number
}

export interface UpdateGroupRequest {
    name: string
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
