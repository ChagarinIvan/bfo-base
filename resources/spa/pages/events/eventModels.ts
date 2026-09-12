import type { AxiosError } from 'axios'
import type { ApiErrorResponse } from '../../api/types'
import { t, type TranslationKey } from '../../i18n'

export function eventErrorMessage(
    exception: unknown,
    fallback: TranslationKey,
): string {
    const response = (exception as AxiosError<ApiErrorResponse>).response
    const code = response?.data?.errors?.[0]?.code

    return code === 'invalid_protocol'
        ? t('spa.errors.invalid_protocol')
        : t(fallback)
}
