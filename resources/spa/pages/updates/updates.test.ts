import { readdirSync, readFileSync } from 'node:fs'
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
        expect(updates[0]?.spec).toBe('041-site-updates')
    })

    it('links to existing SPA routes or checked replacement tasks', () => {
        const router = createAppRouter(createMemoryHistory())

        for (const entry of updates) {
            expect(entry.title).toBeTruthy()
            expect(entry.summary).toBeTruthy()
            expect(entry.details).toBeTruthy()
            expect(entry.date).toMatch(/^\d{4}-\d{2}-\d{2}$/)

            if (!entry.link) continue

            if (entry.link.kind === 'site') {
                const route = router.resolve(entry.link.href)
                expect(route.matched.length).toBeGreaterThan(0)
                expect(route.matched.at(-1)?.path).not.toContain(':pathMatch')
                continue
            }

            const match = entry.link.href.match(
                /^https:\/\/github\.com\/ChagarinIvan\/bfo-base\/blob\/master\/specs\/([^/]+)\/tasks\.md#L(\d+)$/,
            )
            expect(match, entry.spec).not.toBeNull()
            if (!match) continue

            const [, laterSpec, line] = match
            const taskFile = readFileSync(
                new URL(
                    `../../../../specs/${laterSpec}/tasks.md`,
                    import.meta.url,
                ),
                'utf8',
            )
            expect(taskFile.split('\n')[Number(line) - 1]).toMatch(
                /^- \[X\] T\d+/,
            )
        }
    })
})
