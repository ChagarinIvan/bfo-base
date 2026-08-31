import type { CreateCompetitionRequest } from '../../api/types'

export function competitionFormInitialValue(
    value: Partial<CreateCompetitionRequest> = {},
): CreateCompetitionRequest {
    return {
        name: value.name ?? '',
        description: value.description ?? '',
        from: value.from ?? '',
        to: value.to ?? '',
        mass: value.mass ?? false,
    }
}
