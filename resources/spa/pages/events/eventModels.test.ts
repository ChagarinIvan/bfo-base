import { describe, expect, it } from 'vitest'
import { eventErrorMessage } from './eventModels'

describe('event error messages', () => {
    it('explains how to recover from an invalid protocol', () => {
        const exception = {
            response: {
                data: { errors: [{ code: 'invalid_protocol' }] },
            },
        }

        expect(
            eventErrorMessage(exception, 'spa.event.create.error'),
        ).toContain('Праверце файл або спасылку')
    })

    it('uses the operation fallback for other errors', () => {
        expect(eventErrorMessage({}, 'spa.event.create.error')).toBe(
            'Не атрымалася стварыць этап.',
        )
    })
})
