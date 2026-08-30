import translations from '../../resources/lang/by.json'

export type TranslationKey = keyof typeof translations

export function t(
    key: TranslationKey,
    replacements: Record<string, string> = {},
): string {
    let translation = translations[key] ?? key

    for (const [placeholder, value] of Object.entries(replacements)) {
        translation = translation.replace(`:${placeholder}`, value)
    }

    return translation
}
