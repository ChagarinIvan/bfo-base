import { defineStore } from 'pinia'

export type AppearanceMode = 'light' | 'night'

export const appearanceStorageKey = 'spa_appearance_mode'
export const appearanceNightClass = 'app-night-mode'

function browserStorage(): Storage | null {
    try {
        return typeof window === 'undefined' ? null : window.localStorage
    } catch {
        return null
    }
}

export function isAppearanceMode(
    value: string | null,
): value is AppearanceMode {
    return value === 'light' || value === 'night'
}

export function readStoredAppearance(
    storage: Storage | null = browserStorage(),
): AppearanceMode {
    try {
        const value = storage ? storage.getItem(appearanceStorageKey) : null
        return isAppearanceMode(value) ? value : 'light'
    } catch {
        return 'light'
    }
}

export function applyAppearanceMode(
    mode: AppearanceMode,
    root: HTMLElement | null = typeof document === 'undefined'
        ? null
        : document.documentElement,
): void {
    root?.classList.toggle(appearanceNightClass, mode === 'night')
}

function persistAppearance(mode: AppearanceMode): void {
    try {
        browserStorage()?.setItem(appearanceStorageKey, mode)
    } catch {
        // A blocked browser storage must not prevent an in-memory toggle.
    }
}

export const useAppearanceStore = defineStore('appearance', {
    state: () => {
        const mode = readStoredAppearance()
        applyAppearanceMode(mode)

        return { mode }
    },

    actions: {
        setMode(mode: AppearanceMode): void {
            this.mode = mode
            applyAppearanceMode(mode)
            persistAppearance(mode)
        },

        toggle(): void {
            this.setMode(this.mode === 'night' ? 'light' : 'night')
        },
    },
})
