export type CompetitionAction = 'edit' | 'delete'

export function competitionActionRoute(id: string): string {
    return `/app/competitions/${id}/edit`
}
