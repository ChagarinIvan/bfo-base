// @vitest-environment happy-dom

import { beforeEach, describe, expect, it } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import {
    applyAppearanceMode,
    appearanceNightClass,
    appearanceStorageKey,
    readStoredAppearance,
    useAppearanceStore,
} from './appearance'

describe('appearance store', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
        localStorage.clear()
        document.documentElement.classList.remove(appearanceNightClass)
    })

    it('defaults to light and toggles the root marker and storage', () => {
        const store = useAppearanceStore()

        expect(store.mode).toBe('light')
        expect(
            document.documentElement.classList.contains(appearanceNightClass),
        ).toBe(false)

        store.toggle()

        expect(store.mode).toBe('night')
        expect(
            document.documentElement.classList.contains(appearanceNightClass),
        ).toBe(true)
        expect(localStorage.getItem(appearanceStorageKey)).toBe('night')

        store.toggle()

        expect(store.mode).toBe('light')
        expect(
            document.documentElement.classList.contains(appearanceNightClass),
        ).toBe(false)
    })

    it('restores only valid stored values', () => {
        localStorage.setItem(appearanceStorageKey, 'night')
        expect(readStoredAppearance()).toBe('night')

        localStorage.setItem(appearanceStorageKey, 'unknown')
        expect(readStoredAppearance()).toBe('light')
    })

    it('applies a stored mode before the application is mounted', () => {
        applyAppearanceMode('night')

        expect(
            document.documentElement.classList.contains(appearanceNightClass),
        ).toBe(true)
    })

    it('falls back safely when storage is unavailable', () => {
        const throwingStorage = {
            getItem: () => {
                throw new Error('blocked')
            },
        } as unknown as Storage

        expect(readStoredAppearance(throwingStorage)).toBe('light')
    })
})
