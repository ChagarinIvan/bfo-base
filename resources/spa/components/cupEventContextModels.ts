import type { CupEventContext } from '../api/types'

export function contextsByEventId(
    contexts: CupEventContext[],
): Record<string, CupEventContext[]> {
    return contexts.reduce<Record<string, CupEventContext[]>>(
        (result, context) => {
            ;(result[context.eventId] ??= []).push(context)
            return result
        },
        {},
    )
}
