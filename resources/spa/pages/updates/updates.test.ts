import { readdirSync } from 'node:fs'
import { describe, expect, it } from 'vitest'
import { createMemoryHistory } from 'vue-router'
import { createAppRouter } from '../../router'
import { updates } from './updates'

describe('static updates catalog', () => {
    it('has exactly one entry for every specification directory', () => {
        const specs = readdirSync(
            new URL('../../../../specs/', import.meta.url),
            {
                withFileTypes: true,
            },
        )
            .filter((entry) => entry.isDirectory())
            .map((entry) => entry.name)
            .sort()

        expect(updates.map((entry) => entry.spec).sort()).toEqual(specs)
        expect(new Set(updates.map((entry) => entry.spec)).size).toBe(
            updates.length,
        )
    })

    it('sorts by documentation date and then full spec name', () => {
        expect(updates).toEqual(
            [...updates].sort(
                (a, b) =>
                    b.date.localeCompare(a.date) ||
                    b.spec.localeCompare(a.spec),
            ),
        )
        expect(updates[0]?.spec).toBe('043-person-rank-csv-cup-xlsx')
    })

    it('links only to existing SPA routes when a destination is useful', () => {
        const router = createAppRouter(createMemoryHistory())

        for (const entry of updates) {
            expect(entry.title).toBeTruthy()
            expect(entry.summary).toBeTruthy()
            expect(entry.details).toBeTruthy()
            expect(entry.date).toMatch(/^\d{4}-\d{2}-\d{2}$/)

            if (!entry.link) continue

            expect(entry.link.href).toMatch(/^\/app\//)
            const route = router.resolve(entry.link.href)
            expect(route.matched.length).toBeGreaterThan(0)
            expect(route.matched.at(-1)?.path).not.toContain(':pathMatch')
        }
    })

    it('does not invent navigation for parser fixes', () => {
        for (const spec of [
            '010-winorient-parser-nonstarter',
            '023-obelarus-parser-groups',
            '034-albatros-word-html',
            '035-albatros-20260919-parser',
            '036-albatros-20260718-p-p-20-10',
        ]) {
            const entry = updates.find((item) => item.spec === spec)
            expect(entry?.example).toBeUndefined()
            expect(entry?.link).toBeUndefined()
        }
    })
})
