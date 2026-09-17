import type { RankCheck } from '../../api/rankChecks'

export type RankCheckTagSeverity = 'warn' | 'success' | 'danger'

export function rankCheckStatusLabel(status: RankCheck['status']): string {
    return {
        PARSING: 'pending',
        READY: 'ready',
        FAILED: 'failed',
    }[status]
}

export function rankCheckStatusSeverity(
    status: RankCheck['status'],
): RankCheckTagSeverity {
    const severities: Record<RankCheck['status'], RankCheckTagSeverity> = {
        PARSING: 'warn',
        READY: 'success',
        FAILED: 'danger',
    }

    return severities[status]
}
