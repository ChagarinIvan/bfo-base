// @vitest-environment happy-dom
import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import PersonRankAudit from './PersonRankAudit.vue'
import type { RankAuditEntry } from '../../api/personRankAudit'

const { getRankAudit } = vi.hoisted(() => ({
    getRankAudit: vi.fn(),
}))
vi.mock('../../api/personRankAudit', () => ({
    getRankAudit,
}))
const entry: RankAuditEntry = {
    id: '1',
    personId: 7,
    trigger: 'manual',
    created: { at: '2026-10-24T12:00:00+00:00', by: '42' },
    before: {
        current: {
            rankId: 6,
            startedOn: null,
            activatedOn: null,
            finishedOn: null,
        },
        history: [],
        nextActivationOn: null,
    },
    after: {
        current: {
            rankId: 7,
            startedOn: '2026-01-01',
            activatedOn: '2026-01-01',
            finishedOn: '2028-01-01',
        },
        history: [],
        nextActivationOn: null,
    },
}
function render() {
    return mount(PersonRankAudit, {
        props: {
            personId: '7',
            refresh: 0,
            rankLabel: (id: number) => (id === 7 ? 'КМС' : 'I'),
        },
        global: {
            stubs: {
                RouterLink: { template: '<a><slot /></a>' },
                SlicePaginator: true,
                Button: {
                    template:
                        '<button @click="$emit(\'click\')"><slot /></button>',
                },
            },
        },
    })
}
describe('rank audit', () => {
    beforeEach(() => {
        vi.resetAllMocks()
        getRankAudit.mockResolvedValue({
            data: [entry],
            pagination: { currentPage: 1, perPage: 20, hasNext: true },
        })
    })
    it('shows summary, actor, reason and before/after without raw JSON', async () => {
        const wrapper = render()
        await flushPromises()
        expect(wrapper.text()).toContain('КМС')
        expect(wrapper.text()).toContain('Карыстальнік #42')
        expect(wrapper.text()).toContain('Ручны пераразлік')
        expect(wrapper.text()).toContain('Гісторыя не змянілася')
        expect(wrapper.find('pre').exists()).toBe(false)
        wrapper.unmount()
    })
    it('shows history changes and failed attempts from before/after without extra metadata', async () => {
        const row = {
            protocolLineId: 10,
            distanceId: 20,
            eventId: 30,
            competitionId: 40,
            rankId: 7,
            changeType: 'completion',
            achievedOn: '2026-01-01',
            activatedOn: null,
            startedOn: '2026-01-01',
            finishedOn: null,
        }
        getRankAudit.mockResolvedValue({
            data: [
                {
                    ...entry,
                    before: { ...entry.before!, history: [row] },
                    after: {
                        ...entry.after!,
                        history: [{ ...row, activatedOn: '2026-02-01' }],
                    },
                },
            ],
            pagination: { currentPage: 1, perPage: 20, hasNext: false },
        })
        const wrapper = render()
        await flushPromises()
        expect(wrapper.findAll('table')).toHaveLength(2)
        expect(wrapper.text()).toContain('2026-02-01')
        expect(wrapper.text()).toContain('#10')
        getRankAudit.mockResolvedValue({
            data: [{ ...entry, after: null }],
            pagination: { currentPage: 1, perPage: 20, hasNext: false },
        })
        await wrapper.setProps({ refresh: 1 })
        await flushPromises()
        expect(wrapper.text()).toContain('Разлік не завершаны')
        wrapper.unmount()
    })

    it('paginates and reloads when the selected person changes', async () => {
        const wrapper = render()
        await flushPromises()
        wrapper
            .findComponent({ name: 'SlicePaginator' })
            .vm.$emit('page', { page: 1, rows: 10 })
        await flushPromises()
        expect(getRankAudit).toHaveBeenLastCalledWith('7', 2, 10)
        await wrapper.setProps({ personId: '8' })
        await flushPromises()
        expect(getRankAudit).toHaveBeenLastCalledWith('8', 1, 20)
        wrapper.unmount()
    })
    it('distinguishes empty coverage from load failure and supports retry', async () => {
        getRankAudit.mockRejectedValueOnce(new Error('network'))
        const wrapper = render()
        await flushPromises()
        expect(wrapper.text()).toContain('Не ўдалося')
        await wrapper.findAll('button').at(-1)!.trigger('click')
        await flushPromises()
        expect(wrapper.text()).toContain('КМС')
        getRankAudit.mockResolvedValue({
            data: [],
            pagination: { currentPage: 1, perPage: 20, hasNext: false },
        })
        await wrapper.setProps({ refresh: 1 })
        await flushPromises()
        expect(wrapper.text()).toContain('Запісаў пакуль няма')
        wrapper.unmount()
    })
})
