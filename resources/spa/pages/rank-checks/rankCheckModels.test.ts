import { describe, expect, it } from 'vitest'
import {
    rankCheckStatusLabel,
    rankCheckStatusSeverity,
} from './rankCheckModels'

describe('rank check statuses', () => {
    it.each([
        ['PARSING', 'Pending', 'warn'],
        ['READY', 'Ready', 'success'],
        ['FAILED', 'Failed', 'danger'],
    ] as const)(
        '%s has a capitalized label and semantic severity',
        (status, label, severity) => {
            expect(rankCheckStatusLabel(status)).toBe(label)
            expect(rankCheckStatusSeverity(status)).toBe(severity)
        },
    )
})
