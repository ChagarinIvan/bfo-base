import { t, type TranslationKey } from '../i18n'

export const CUP_TYPES = [
    'elite',
    'master',
    'sprint',
    'bike',
    'juniors',
    'youth',
    'new_youth',
    'new_master',
    'ski',
    'elk_path',
] as const

export type CupType = (typeof CUP_TYPES)[number]

export interface CupTypeDefinition {
    type: string
    icon: string
    label: TranslationKey
    fallback: boolean
}

const definitions: Record<CupType, CupTypeDefinition> = {
    elite: {
        type: 'elite',
        icon: 'fas fa-running',
        label: 'app.cup.type.elite',
        fallback: false,
    },
    master: {
        type: 'master',
        icon: 'fas fa-running',
        label: 'app.cup.type.master',
        fallback: false,
    },
    sprint: {
        type: 'sprint',
        icon: 'fas fa-bolt',
        label: 'app.cup.type.sprint',
        fallback: false,
    },
    bike: {
        type: 'bike',
        icon: 'fas fa-bicycle',
        label: 'app.cup.type.bike',
        fallback: false,
    },
    juniors: {
        type: 'juniors',
        icon: 'fas fa-child',
        label: 'app.cup.type.juniors',
        fallback: false,
    },
    youth: {
        type: 'youth',
        icon: 'fas fa-child',
        label: 'app.cup.type.youth',
        fallback: false,
    },
    new_youth: {
        type: 'new_youth',
        icon: 'fas fa-child',
        label: 'app.cup.type.new_youth',
        fallback: false,
    },
    new_master: {
        type: 'new_master',
        icon: 'fas fa-running',
        label: 'app.cup.type.new_master',
        fallback: false,
    },
    ski: {
        type: 'ski',
        icon: 'fas fa-skiing',
        label: 'app.cup.type.ski',
        fallback: false,
    },
    elk_path: {
        type: 'elk_path',
        icon: 'fas fa-mountain',
        label: 'app.cup.type.elk_path',
        fallback: false,
    },
}

export const cupTypeDefinitions = definitions

const fallbackDefinition: CupTypeDefinition = {
    type: 'unknown',
    icon: 'fas fa-question-circle',
    label: 'spa.cup.type.unknown',
    fallback: true,
}

export function cupTypeDefinition(type: string): CupTypeDefinition {
    return Object.prototype.hasOwnProperty.call(definitions, type)
        ? definitions[type as CupType]
        : fallbackDefinition
}

export function cupTypeLabel(type: string): string {
    return t(cupTypeDefinition(type).label)
}
