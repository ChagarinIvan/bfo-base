import { existsSync, readdirSync, readFileSync } from 'node:fs'
import { describe, expect, it } from 'vitest'
import { createMemoryHistory } from 'vue-router'
import { createAppRouter } from '../../router'
import { updates } from './updates'

describe('static updates catalog', () => {
    it('has one entry for each completed specification without duplicates', () => {
        const specsDir = new URL('../../../../specs/', import.meta.url)
        const specs = readdirSync(specsDir, { withFileTypes: true })
            .filter(
                (entry) =>
                    entry.isDirectory() &&
                    existsSync(new URL(`${entry.name}/spec.md`, specsDir)),
            )
            .map((entry) => entry.name)

        const completed = specs.filter((spec) => {
            const tasksPath = new URL(`${spec}/tasks.md`, specsDir)
            if (!existsSync(tasksPath)) return false

            const tasks = readFileSync(tasksPath, 'utf8')
            return (
                /^\s*- \[x\] T\d+/im.test(tasks) &&
                !/^\s*- \[ \] T\d+/m.test(tasks)
            )
        })
        const published = updates.map((entry) => entry.spec)
        expect(published).toEqual(expect.arrayContaining(completed))
        expect(specs).toEqual(expect.arrayContaining(published))
        expect(new Set(published).size).toBe(updates.length)
    })

    it('omits the disabled review and dates manual ranks by their release', () => {
        expect(
            updates.some((entry) => entry.spec === '055-agent-pr-review'),
        ).toBe(false)
        expect(
            updates.find((entry) => entry.spec === '056-manual-person-ranks')
                ?.date,
        ).toBe('2026-11-23')
    })

    it('sorts by documentation date and then full spec name', () => {
        expect(updates).toEqual(
            [...updates].sort(
                (a, b) =>
                    b.date.localeCompare(a.date) ||
                    b.spec.localeCompare(a.spec),
            ),
        )
        expect(updates[0]).toMatchObject({
            spec: '058-albatros-gomel-birth-year',
            date: '2026-11-26',
        })
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
            '058-albatros-gomel-birth-year',
        ]) {
            const entry = updates.find((item) => item.spec === spec)
            expect(entry?.example).toBeUndefined()
            expect(entry?.link).toBeUndefined()
        }
    })
})
