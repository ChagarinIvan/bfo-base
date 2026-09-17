import { describe, expect, it } from 'vitest'
import {
    rankCheckStatusLabel,
    rankCheckStatusSeverity,
} from './rankCheckModels'

describe('rank check statuses', () => {
    it.each([
        ['PARSING', 'pending', 'warn'],
        ['READY', 'ready', 'success'],
        ['FAILED', 'failed', 'danger'],
    ] as const)(
        '%s has a lowercase label and semantic severity',
        (status, label, severity) => {
            expect(rankCheckStatusLabel(status)).toBe(label)
            expect(rankCheckStatusSeverity(status)).toBe(severity)
        },
    )
})
